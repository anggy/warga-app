const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const BaileysClient = require('./baileys');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*", // Allow all origins for dev
        methods: ["GET", "POST"]
    }
});

app.use(cors());
app.use(express.json());

// Session Management
const sessions = new Map();

// Helper to create/load session
const initSession = async (id) => {
    if (sessions.has(id)) return sessions.get(id);

    console.log(`Initializing session: ${id}`);
    const client = new BaileysClient(io, id);
    await client.start();
    sessions.set(id, client);
    return client;
};

// API Endpoints for Sessions header
app.get('/api/sessions', (req, res) => {
    const sessionList = Array.from(sessions.entries()).map(([id, client]) => ({
        id,
        status: client.isConnected ? 'connected' : 'disconnected',
        user: client.sock?.user
    }));
    res.json({ success: true, sessions: sessionList });
});

app.post('/api/sessions', async (req, res) => {
    const { id } = req.body;
    if (!id) return res.status(400).json({ error: 'Session ID required' });

    try {
        await initSession(id);
        res.json({ success: true, message: `Session ${id} created` });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/api/sessions/:id', async (req, res) => {
    const { id } = req.params;
    const client = sessions.get(id);

    if (client) {
        await client.logout();
        sessions.delete(id);
        res.json({ success: true, message: 'Session deleted' });
    } else {
        res.status(404).json({ error: 'Session not found' });
    }
});

app.get('/api/status/:sessionId', (req, res) => {
    const { sessionId } = req.params;
    const client = sessions.get(sessionId);
    if (!client) return res.status(404).json({ status: 'not_found' });
    res.json(client.getStatus());
});

app.post('/api/send', async (req, res) => {
    const { sessionId, number, message } = req.body;

    if (!sessionId) return res.status(400).json({ success: false, error: 'Session ID required' });

    const client = sessions.get(sessionId);
    if (!client) return res.status(404).json({ success: false, error: 'Session not active' });

    try {
        await client.sendMessage(number, message);
        res.json({ success: true, message: 'Message sent' });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

app.get('/api/logout/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const client = sessions.get(sessionId);
    if (client) {
        await client.logout();
        // Don't delete from map immediately? or do? 
        // User asked for logout, usually means disconnect but keep config? 
        // For now, let's keep it simple: logout disconnects socket.
        // If hard delete is needed, use DELETE /api/sessions/:id
        res.json({ success: true });
    } else {
        res.status(404).json({ error: 'Session not found' });
    }
});

app.get('/api/groups/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const client = sessions.get(sessionId);
    if (!client) return res.status(404).json({ error: 'Session not found' });

    try {
        const groups = await client.getGroups();
        res.json({ success: true, groups });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Rules API
const rulesManager = require('./rules');

app.get('/api/rules', (req, res) => {
    res.json({ success: true, rules: rulesManager.getRules() });
});

app.post('/api/rules', (req, res) => {
    const { keyword, response, type } = req.body;
    if (!keyword || !response) {
        return res.status(400).json({ success: false, error: 'Keyword and response required' });
    }
    const newRule = rulesManager.addRule({ keyword, response, type: type || 'exact' });
    // Update client rules (in memory) if needed, or client reads from file on each msg
    sessions.forEach(sessionClient => sessionClient.reloadRules());
    res.json({ success: true, rule: newRule });
});

app.delete('/api/rules/:id', (req, res) => {
    const success = rulesManager.deleteRule(req.params.id);
    if (success) {
        sessions.forEach(sessionClient => sessionClient.reloadRules());
        res.json({ success: true });
    } else {
        res.status(404).json({ success: false, error: 'Rule not found' });
    }
});
// Internal Shutdown API
app.post('/api/shutdown', (req, res) => {
    res.json({ success: true, message: 'Shutting down server...' });
    console.log('Received shutdown signal via API');

    // Graceful exit
    setTimeout(() => {
        sessions.forEach(client => client.logout()); // Try disconnect everyone
        process.exit(0);
    }, 1000);
});

// Socket.io connection handling
io.on('connection', (socket) => {
    console.log('New client connected');

    // Client joins a session room
    socket.on('join-session', (sessionId) => {
        socket.join(sessionId);
        const session = sessions.get(sessionId);
        if (session) {
            const status = session.getStatus();
            socket.emit('status', { status: status.status, user: status.user });
            if (status.qr) socket.emit('qr', status.qr);
        } else {
            socket.emit('error', 'Session not found');
        }
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected');
    });
});

// Initialize a default session for backward compatibility or easy start
initSession('default');

const PORT = 3001;
server.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
