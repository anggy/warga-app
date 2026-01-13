import { useState, useEffect } from 'react';
import { io } from 'socket.io-client';
import { QRCodeCanvas } from 'qrcode.react';
import { FaWhatsapp, FaRobot, FaPaperPlane, FaSignOutAlt, FaCircle } from 'react-icons/fa';
import axios from 'axios';
import SessionManager from './components/SessionManager';

const socket = io('http://localhost:3001');

function App() {
  const [activeSession, setActiveSession] = useState('default');

  const [socketConnected, setSocketConnected] = useState(false);
  const [status, setStatus] = useState('disconnected');
  const [qr, setQr] = useState('');
  const [user, setUser] = useState(null);
  const [logs, setLogs] = useState([]);

  // Message Sending State
  const [phone, setPhone] = useState('');
  const [message, setMessage] = useState('');
  const [sending, setSending] = useState(false);

  // Groups State
  const [groups, setGroups] = useState([]);
  const [loadingGroups, setLoadingGroups] = useState(false);

  // Rules State
  const [rules, setRules] = useState([]);
  const [newRule, setNewRule] = useState({ keyword: '', response: '', type: 'exact' });
  const [activeTab, setActiveTab] = useState('groups'); // 'groups' or 'rules'

  useEffect(() => {
    socket.on('connect', () => {
      console.log('Connected to server socket');
      setSocketConnected(true);
      addLog('System', 'Connected to dashboard server');
      // Re-join session room on reconnect
      if (activeSession) socket.emit('join-session', activeSession);
    });

    socket.on('disconnect', () => {
      console.log('Disconnected from server socket');
      setSocketConnected(false);
      addLog('System', 'Disconnected from dashboard server');
    });

    socket.on('connect_error', (err) => {
      console.error('Socket connection error:', err);
      addLog('Error', `Socket Error: ${err.message}`);
    });

    socket.on('status', (data) => {
      setStatus(data.status);
      setUser(data.user);
      if (data.status === 'connected') {
        addLog('System', 'Bot connected to WhatsApp');
      } else {
        addLog('System', 'Bot disconnected');
      }
    });

    socket.on('qr', (data) => {
      setQr(data);
      if (data) addLog('System', 'New QR Code received');
    });

    return () => {
      socket.off('connect');
      socket.off('disconnect');
      socket.off('connect_error');
      socket.off('status');
      socket.off('qr');
    };
  }, [activeSession]);

  // Join session room when activeSession changes
  useEffect(() => {
    if (activeSession && socketConnected) {
      // Reset state for new session view
      setStatus('disconnected');
      setQr('');
      setUser(null);
      setGroups([]);

      socket.emit('join-session', activeSession);
      addLog('System', `Switched to session: ${activeSession}`);

      // Fetch initial data for this session
      fetchStatus();
    }
  }, [activeSession, socketConnected]);

  // Initial fetch for rules when tab changes
  useEffect(() => {
    if (activeTab === 'rules') fetchRules();
  }, [activeTab]);

  const addLog = (source, text) => {
    setLogs(prev => [...prev.slice(-4), { source, text, time: new Date().toLocaleTimeString() }]);
  };

  const fetchStatus = async () => {
    try {
      const res = await axios.get(`http://localhost:3001/api/status/${activeSession}`);
      setStatus(res.data.status);
      setUser(res.data.user);
      if (res.data.qr) setQr(res.data.qr);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchGroups = async () => {
    setLoadingGroups(true);
    try {
      const res = await axios.get(`http://localhost:3001/api/groups/${activeSession}`);
      setGroups(res.data.groups);
      addLog('System', `Fetched ${res.data.groups.length} groups`);
    } catch (err) {
      addLog('Error', `Failed to fetch groups: ${err.message}`);
    } finally {
      setLoadingGroups(false);
    }
  };

  const fetchRules = async () => {
    try {
      // Assuming rules are global for now, but good practice to anticipate session-specificity
      const res = await axios.get('http://localhost:3001/api/rules');
      setRules(res.data.rules);
    } catch (err) {
      console.error(err);
    }
  };

  const handleAddRule = async (e) => {
    e.preventDefault();
    if (!newRule.keyword || !newRule.response) return;
    try {
      await axios.post('http://localhost:3001/api/rules', newRule);
      setNewRule({ keyword: '', response: '', type: 'exact' });
      fetchRules();
      addLog('System', 'New auto-reply rule added');
    } catch (err) {
      addLog('Error', 'Failed to add rule');
    }
  };

  const handleDeleteRule = async (id) => {
    try {
      await axios.delete(`http://localhost:3001/api/rules/${id}`);
      fetchRules();
      addLog('System', 'Rule deleted');
    } catch (err) {
      addLog('Error', 'Failed to delete rule');
    }
  };

  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    addLog('System', 'ID copied to clipboard');
  };

  const handleSend = async (e) => {
    e.preventDefault();
    if (!phone || !message) return;
    setSending(true);
    try {
      await axios.post('http://localhost:3001/api/send', { sessionId: activeSession, number: phone, message });
      addLog('Outbound', `Message sent to ${phone}`);
      setMessage('');
    } catch (err) {
      addLog('Error', `Failed to send: ${err.message}`);
    } finally {
      setSending(false);
    }
  };

  const handleLogout = async () => {
    if (confirm('Are you sure you want to logout the bot?')) {
      try {
        await axios.get(`http://localhost:3001/api/logout/${activeSession}`);
      } catch (err) {
        console.error(err);
      }
    }
  };

  return (
    <div className="min-h-screen bg-dark-bg text-gray-100 flex items-center justify-center p-4">
      <div className="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-3 gap-8">

        {/* Left Panel: Session & Status */}
        <div className="lg:col-span-1 space-y-6">

          <SessionManager
            activeSession={activeSession}
            setActiveSession={setActiveSession}
            onSessionChange={(id) => setActiveSession(id)}
          />

          <div className="bg-dark-paper p-6 rounded-2xl shadow-xl border border-gray-800">
            <div className="flex items-center space-x-3 mb-6">
              <div className="p-3 bg-whatsapp rounded-lg">
                <FaWhatsapp className="text-2xl text-white" />
              </div>
              <div>
                <h1 className="text-xl font-bold">WaBot Dashboard</h1>
                <p className="text-xs text-gray-400">Unofficial Manager</p>
              </div>
            </div>

            <div className="space-y-4">
              {/* Socket Status */}
              <div className="flex items-center justify-between p-4 bg-dark-input rounded-xl border border-gray-700">
                <span className="text-sm text-gray-400">Server Status</span>
                <div className={`flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium ${socketConnected ? 'bg-blue-900/30 text-blue-400' : 'bg-orange-900/30 text-orange-400'}`}>
                  <FaCircle className="text-[8px]" />
                  <span className="uppercase">{socketConnected ? 'Online' : 'Offline'}</span>
                </div>
              </div>

              <div className="flex items-center justify-between p-4 bg-dark-input rounded-xl">
                <span className="text-sm text-gray-400">WhatsApp Status</span>
                <div className={`flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium ${status === 'connected' ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400'}`}>
                  <FaCircle className="text-[8px]" />
                  <span className="uppercase">{status}</span>
                </div>
              </div>

              {user && (
                <div className="p-4 bg-dark-input rounded-xl border border-gray-700">
                  <p className="text-xs text-gray-500 mb-1">Connected as</p>
                  <p className="font-semibold text-lg">{user.name || user.id}</p>
                </div>
              )}

              {status === 'connected' && (
                <button onClick={handleLogout} className="w-full flex items-center justify-center space-x-2 p-3 bg-red-600/10 hover:bg-red-600/20 text-red-500 rounded-xl transition-all">
                  <FaSignOutAlt />
                  <span>Logout Session</span>
                </button>
              )}
            </div>
          </div>

          {/* Logs Preview */}
          <div className="bg-dark-paper p-6 rounded-2xl shadow-xl border border-gray-800">
            <h3 className="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Recent Activity</h3>
            <div className="space-y-3 h-48 overflow-y-auto custom-scrollbar">
              {logs.length === 0 && <p className="text-sm text-gray-600 italic">No activity yet...</p>}
              {logs.map((log, i) => (
                <div key={i} className="text-xs border-l-2 border-gray-700 pl-3 py-1">
                  <p className="text-gray-500 mb-0.5">{log.time} • <span className="text-whatsapp">{log.source}</span></p>
                  <p className="text-gray-300">{log.text}</p>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Middle Panel: Send Message */}
        <div className="lg:col-span-1">
          <div className="bg-dark-paper p-8 rounded-2xl shadow-xl border border-gray-800 h-full flex flex-col justify-center items-center">
            {!activeSession ? (
              <div className="text-center">
                <h2 className="text-xl font-bold mb-2">No Session Selected</h2>
                <p className="text-gray-400">Create or select a session to start.</p>
              </div>
            ) : !socketConnected ? (
              <div className="text-center">
                <div className="text-orange-500 text-4xl mb-4 flex justify-center"><FaSignOutAlt /></div>
                <h2 className="text-xl font-bold mb-2">Server Disconnected</h2>
                <p className="text-gray-400">Ensure backend server is running.</p>
              </div>
            ) : status === 'disconnected' && qr ? (
              <div className="text-center animate-fade-in">
                <h2 className="text-2xl font-bold mb-2">Scan QR ({activeSession})</h2>
                <div className="bg-white p-4 rounded-xl inline-block shadow-lg mx-auto mb-4">
                  <QRCodeCanvas value={qr} size={200} />
                </div>
                <p className="text-sm text-gray-500">Refresh if QR expires</p>
              </div>
            ) : status === 'connected' ? (
              <div className="w-full">
                <div className="mb-6 p-4 bg-whatsapp/10 rounded-xl border border-whatsapp/20 flex items-center space-x-3">
                  <div className="p-2 bg-whatsapp rounded-full bg-opacity-20 text-whatsapp">
                    <FaRobot className="text-xl" />
                  </div>
                  <div>
                    <h2 className="text-lg font-bold text-whatsapp-teal">System Ready</h2>
                    <p className="text-gray-400 text-xs">Bot ({activeSession}) is listening.</p>
                  </div>
                </div>

                <form onSubmit={handleSend} className="space-y-4">
                  <h3 className="text-md font-semibold border-b border-gray-700 pb-2">Send Message</h3>

                  <div>
                    <label className="block text-xs font-medium text-gray-400 mb-1 uppercase">Phone / Group ID</label>
                    <input
                      type="text"
                      placeholder="e.g. 62812345678 or 123...456@g.us"
                      className="w-full bg-dark-input border border-gray-700 rounded-lg p-3 focus:outline-none focus:border-whatsapp transition-colors text-white placeholder-gray-600 text-sm"
                      value={phone}
                      onChange={e => setPhone(e.target.value)}
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-400 mb-1 uppercase">Message</label>
                    <textarea
                      rows="4"
                      placeholder="Type your message here..."
                      className="w-full bg-dark-input border border-gray-700 rounded-lg p-3 focus:outline-none focus:border-whatsapp transition-colors text-white placeholder-gray-600 resize-none text-sm"
                      value={message}
                      onChange={e => setMessage(e.target.value)}
                    ></textarea>
                  </div>

                  <button
                    type="submit"
                    disabled={sending}
                    className={`w-full py-3 rounded-lg font-bold flex items-center justify-center space-x-2 transition-all ${sending ? 'bg-gray-700 cursor-not-allowed' : 'bg-whatsapp hover:bg-whatsapp-dark text-white'}`}
                  >
                    <FaPaperPlane />
                    <span>{sending ? 'Sending...' : 'Send'}</span>
                  </button>
                </form>
              </div>
            ) : (
              <div className="text-center text-gray-500">
                <div className="w-8 h-8 border-2 border-gray-700 border-t-whatsapp rounded-full animate-spin mx-auto mb-2"></div>
                <p>Loading session...</p>
              </div>
            )}
          </div>
        </div>

        {/* Right Panel: Groups & Rules (Tabbed) */}
        <div className="lg:col-span-1">
          <div className="bg-dark-paper p-6 rounded-2xl shadow-xl border border-gray-800 h-full flex flex-col">
            <div className="flex space-x-2 mb-4 border-b border-gray-700 pb-2">
              <button
                onClick={() => setActiveTab('groups')}
                className={`flex-1 py-2 text-sm font-semibold rounded-lg transition-colors ${activeTab === 'groups' ? 'bg-whatsapp text-white' : 'text-gray-400 hover:bg-dark-input'}`}
              >
                Groups
              </button>
              <button
                onClick={() => setActiveTab('rules')}
                className={`flex-1 py-2 text-sm font-semibold rounded-lg transition-colors ${activeTab === 'rules' ? 'bg-whatsapp text-white' : 'text-gray-400 hover:bg-dark-input'}`}
              >
                Auto-Reply
              </button>
            </div>

            <div className="flex-1 overflow-y-auto custom-scrollbar space-y-2 pr-2">
              {activeTab === 'groups' ? (
                <>
                  <div className="flex justify-between items-center mb-3">
                    <h3 className="text-xs font-bold uppercase text-gray-500">My Groups</h3>
                    <button
                      onClick={fetchGroups}
                      disabled={status !== 'connected' || loadingGroups}
                      className="text-[10px] text-whatsapp hover:underline disabled:opacity-50"
                    >
                      {loadingGroups ? 'Loading...' : 'Refresh List'}
                    </button>
                  </div>
                  {status !== 'connected' ? (
                    <p className="text-center text-gray-500 text-sm mt-10">Connect bot to view groups.</p>
                  ) : groups.length === 0 ? (
                    <p className="text-center text-gray-500 text-xs">No groups found or not fetched yet.</p>
                  ) : (
                    <div className="space-y-2">
                      {groups.map(group => (
                        <div key={group.id} className="bg-dark-input p-3 rounded-xl border border-gray-700 hover:border-whatsapp transition-colors">
                          <div className="flex justify-between items-start mb-1">
                            <h4 className="font-semibold text-sm truncate w-3/4" title={group.subject}>{group.subject}</h4>
                            <span className="text-[10px] bg-gray-700 px-1.5 py-0.5 rounded text-gray-300">{group.participants ? group.participants.length : group.count} members</span>
                          </div>
                          <div className="flex justify-between items-center text-gray-500">
                            <p className="text-[10px] truncate w-2/3">{group.id}</p>
                            <button onClick={() => { copyToClipboard(group.id); setPhone(group.id); }} className="text-[10px] text-whatsapp hover:underline">Copy ID</button>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </>
              ) : (
                <>
                  <form onSubmit={handleAddRule} className="mb-6 bg-dark-input p-3 rounded-xl border border-gray-700">
                    <h3 className="text-xs font-bold uppercase text-gray-500 mb-2">New Rule</h3>
                    <input
                      className="w-full bg-dark-bg border border-gray-700 rounded text-xs p-2 mb-2 focus:border-whatsapp outline-none"
                      placeholder="Keyword (e.g. !help)"
                      value={newRule.keyword}
                      onChange={e => setNewRule({ ...newRule, keyword: e.target.value })}
                    />
                    <input
                      className="w-full bg-dark-bg border border-gray-700 rounded text-xs p-2 mb-2 focus:border-whatsapp outline-none"
                      placeholder="Reply Message"
                      value={newRule.response}
                      onChange={e => setNewRule({ ...newRule, response: e.target.value })}
                    />
                    <div className="flex justify-between items-center">
                      <select
                        className="bg-dark-bg border border-gray-700 rounded text-xs p-1 outline-none"
                        value={newRule.type}
                        onChange={e => setNewRule({ ...newRule, type: e.target.value })}
                      >
                        <option value="exact">Exact Match</option>
                        <option value="contains">Contains</option>
                      </select>
                      <button type="submit" className="bg-whatsapp hover:bg-green-600 text-white text-xs px-3 py-1 rounded transition-colors">Add</button>
                    </div>
                  </form>

                  <h3 className="text-xs font-bold uppercase text-gray-500 mb-2">Active Rules</h3>
                  {rules.length === 0 ? <p className="text-center text-gray-500 text-xs">No rules configured.</p> : (
                    <div className="space-y-2">
                      {rules.map(rule => (
                        <div key={rule.id} className="bg-dark-input p-3 rounded-xl border border-gray-700 flex justify-between items-center">
                          <div className="overflow-hidden">
                            <p className="text-sm font-bold text-whatsapp mb-0.5">{rule.keyword}</p>
                            <p className="text-xs text-gray-400 truncate">{rule.response}</p>
                            <span className="text-[8px] bg-gray-700 px-1 rounded text-gray-300 uppercase">{rule.type}</span>
                          </div>
                          <button onClick={() => handleDeleteRule(rule.id)} className="text-red-500 hover:text-red-400 text-xs ml-2">Delete</button>
                        </div>
                      ))}
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        </div>

      </div>
    </div>
  )
}

export default App
