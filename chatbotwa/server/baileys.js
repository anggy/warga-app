const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore
} = require('@whiskeysockets/baileys');
const pino = require('pino');
const path = require('path');
const fs = require('fs');

class BaileysClient {
    constructor(io, sessionId) {
        this.io = io;
        this.sessionId = sessionId;
        this.sock = null;
        this.authDir = path.join(__dirname, 'sessions', `auth_${sessionId}`);
        this.isConnected = false;
        this.qr = '';
        this.rules = [];

        // Ensure sessions directory exists
        const sessionsDir = path.join(__dirname, 'sessions');
        if (!fs.existsSync(sessionsDir)) {
            fs.mkdirSync(sessionsDir, { recursive: true });
        }
    }

    async start() {
        // Ensure auth directory management
        const { state, saveCreds } = await useMultiFileAuthState(this.authDir);
        const { version } = await fetchLatestBaileysVersion();

        this.sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            printQRInTerminal: true,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: "silent" }))
            },
            browser: ['Chatbot Dashboard', 'Chrome', '1.0.0'],
            generateHighQualityLinkPreview: true,
            syncFullHistory: false, // improving performance
            retryRequestDelayMs: 2000,
            keepAliveIntervalMs: 10000,
        });

        this.sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            // Emit events to specific room for this session
            const emitUpdate = (event, data) => {
                this.io.to(this.sessionId).emit(event, data);
                // Also emit to global listener with ID attached (optional, for session list updates)
                this.io.emit('session:update', { sessionId: this.sessionId, event, data });
            };

            if (qr) {
                this.qr = qr;
                emitUpdate('qr', qr);
                console.log(`[${this.sessionId}] QR Received`);
            }

            if (connection === 'close') {
                const error = lastDisconnect?.error;
                const statusCode = error?.output?.statusCode;

                // Detect specific Stream Error (xml-not-well-formed)
                const isStreamError = error?.message?.includes('xml-not-well-formed');
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut && !isStreamError;

                console.log(`[${this.sessionId}] connection closed due to `, error, ', reconnecting ', shouldReconnect);

                this.isConnected = false;
                emitUpdate('status', { status: 'disconnected' });

                if (isStreamError) {
                    console.error(`[${this.sessionId}] CRITICAL: Stream Error. Resetting...`);
                    await this.hardReset();
                } else if (shouldReconnect) {
                    this.start();
                }
            } else if (connection === 'open') {
                console.log(`[${this.sessionId}] opened connection`);
                this.isConnected = true;
                this.qr = '';
                emitUpdate('status', { status: 'connected', user: this.sock.user });
                emitUpdate('qr', ''); // Clear QR
            }
        });

        this.sock.ev.on('creds.update', saveCreds);

        this.sock.ev.on('messages.upsert', async (m) => {
            try {
                const msg = m.messages[0];
                if (!msg.message || msg.key.fromMe) return;

                const text = msg.message.conversation || msg.message.extendedTextMessage?.text || '';
                if (!text) return;

                const remoteJid = msg.key.remoteJid;
                console.log(`Received message from ${remoteJid}: ${text}`);

                this.checkRulesAndReply(remoteJid, text);
            } catch (err) {
                console.error('Error handling message:', err);
            }
        });

        this.reloadRules();
    }

    reloadRules() {
        try {
            const rulesManager = require('./rules');
            this.rules = rulesManager.getRules();
            console.log(`Loaded ${this.rules.length} auto-reply rules`);
        } catch (err) {
            console.error('Failed to load rules:', err);
            this.rules = [];
        }
    }

    async checkRulesAndReply(jid, text) {
        if (!this.rules) return;

        const lowerText = text.toLowerCase().trim();

        for (const rule of this.rules) {
            const keyword = rule.keyword.toLowerCase();
            let match = false;

            if (rule.type === 'exact') {
                match = lowerText === keyword;
            } else if (rule.type === 'contains') {
                match = lowerText.includes(keyword);
            }

            if (match) {
                console.log(`Match found! Rule: ${rule.keyword} -> Reply: ${rule.response}`);
                await this.sendMessage(jid, rule.response);
                break; // Stop after first match (optional)
            }
        }
    }

    async hardReset() {
        try {
            if (this.sock) {
                this.sock.end(undefined); // Close socket gracefully
                // Wait for socket to fully close and file locks to release
                await new Promise(resolve => setTimeout(resolve, 2000));
            }

            // Remove auth directory with retry logic for Windows file locks
            try {
                fs.rmSync(this.authDir, { recursive: true, force: true });
            } catch (e) {
                console.log('First delete attempt failed, retrying in 2s...');
                await new Promise(resolve => setTimeout(resolve, 2000));
                fs.rmSync(this.authDir, { recursive: true, force: true });
            }

            console.log('Session cleared. Restarting...');
            this.start();
        } catch (err) {
            console.error('Failed to reset session:', err);
        }
    }

    async sendMessage(number, text) {
        if (!this.isConnected) {
            throw new Error('Client not connected');
        }

        let id = number;
        // If it doesn't look like a JID (no '@'), assume it's a phone number
        if (!id.includes('@')) {
            id = id + '@s.whatsapp.net';
        }
        // If it is a group ID (@g.us) or already has @s.whatsapp.net, leave it as is.

        await this.sock.sendMessage(id, { text });
    }

    getStatus() {
        return {
            status: this.isConnected ? 'connected' : 'disconnected',
            qr: this.qr,
            user: this.sock?.user
        };
    }

    async getGroups() {
        if (!this.isConnected) {
            throw new Error('Client not connected');
        }
        // Fetch all groups where the bot is a participant
        const groups = await this.sock.groupFetchAllParticipating();
        return Object.values(groups).map(g => ({
            id: g.id,
            subject: g.subject,
            count: g.participants.length
        }));
    }

    async logout() {
        if (this.sock) {
            await this.sock.logout();
            // Cleanup auth dir can be done here if "logout" means full reset
            // fs.rmSync(this.authDir, { recursive: true, force: true }); 
        }
    }
}

module.exports = BaileysClient;
