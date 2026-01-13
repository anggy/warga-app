import { useState, useEffect } from 'react';
import axios from 'axios';
import { FaPlus, FaRobot, FaTrash } from 'react-icons/fa';

function SessionManager({ activeSession, setActiveSession, onSessionChange }) {
    const [sessions, setSessions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [newSessionId, setNewSessionId] = useState('');
    const [isCreating, setIsCreating] = useState(false);

    const fetchSessions = async () => {
        try {
            const res = await axios.get('http://localhost:3001/api/sessions');
            setSessions(res.data.sessions);
            // If no active session, select the first one or default
            if (!activeSession && res.data.sessions.length > 0) {
                // setActiveSession(res.data.sessions[0].id); // Optional: Auto-select
            }
        } catch (err) {
            console.error('Failed to fetch sessions', err);
        }
    };

    useEffect(() => {
        fetchSessions();
        const interval = setInterval(fetchSessions, 5000); // Polling for status updates
        return () => clearInterval(interval);
    }, []);

    const handleCreateSession = async (e) => {
        e.preventDefault();
        if (!newSessionId) return;
        setLoading(true);
        try {
            await axios.post('http://localhost:3001/api/sessions', { id: newSessionId });
            await fetchSessions();
            setActiveSession(newSessionId);
            onSessionChange(newSessionId);
            setNewSessionId('');
            setIsCreating(false);
        } catch (err) {
            alert('Failed to create session: ' + err.response?.data?.error || err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteSession = async (id, e) => {
        e.stopPropagation();
        if (!confirm(`Delete session "${id}"? This will logout the bot.`)) return;
        try {
            await axios.delete(`http://localhost:3001/api/sessions/${id}`);
            fetchSessions();
            if (activeSession === id) {
                setActiveSession('');
                onSessionChange('');
            }
        } catch (err) {
            alert('Failed to delete session');
        }
    };

    return (
        <div className="bg-dark-paper p-4 rounded-2xl shadow-xl border border-gray-800 mb-6">
            <div className="flex justify-between items-center mb-4">
                <h3 className="font-bold text-gray-400 text-xs uppercase tracking-wider">Active Sessions</h3>
                <button
                    onClick={() => setIsCreating(!isCreating)}
                    className="text-xs bg-whatsapp/20 text-whatsapp hover:bg-whatsapp/30 px-2 py-1 rounded transition-colors flex items-center space-x-1"
                >
                    <FaPlus size={10} /> <span>New</span>
                </button>
            </div>

            {isCreating && (
                <form onSubmit={handleCreateSession} className="mb-4 flex space-x-2 animate-fade-in">
                    <input
                        className="flex-1 bg-dark-input border border-gray-700 rounded text-xs p-2 focus:border-whatsapp outline-none"
                        placeholder="Session Name (e.g. Sales)"
                        value={newSessionId}
                        onChange={e => setNewSessionId(e.target.value)}
                        autoFocus
                    />
                    <button
                        type="submit"
                        disabled={loading}
                        className="bg-whatsapp hover:bg-green-600 text-white text-xs px-3 py-1 rounded transition-colors"
                    >
                        {loading ? '...' : 'Add'}
                    </button>
                </form>
            )}

            <div className="space-y-2 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                {sessions.map(session => (
                    <div
                        key={session.id}
                        onClick={() => { setActiveSession(session.id); onSessionChange(session.id); }}
                        className={`p-3 rounded-xl border cursor-pointer transition-all flex justify-between items-center ${activeSession === session.id ? 'bg-whatsapp/10 border-whatsapp' : 'bg-dark-input border-gray-700 hover:border-gray-500'}`}
                    >
                        <div className="flex items-center space-x-3">
                            <div className={`p-2 rounded-full ${session.status === 'connected' ? 'bg-green-500/20 text-green-500' : 'bg-gray-700 text-gray-400'}`}>
                                <FaRobot />
                            </div>
                            <div>
                                <p className={`text-sm font-bold ${activeSession === session.id ? 'text-white' : 'text-gray-300'}`}>{session.id}</p>
                                <p className="text-[10px] text-gray-500 uppercase">{session.status}</p>
                            </div>
                        </div>
                        {activeSession !== session.id && (
                            <button
                                onClick={(e) => handleDeleteSession(session.id, e)}
                                className="text-red-500/50 hover:text-red-500 p-2"
                            >
                                <FaTrash size={12} />
                            </button>
                        )}
                    </div>
                ))}
                {sessions.length === 0 && <p className="text-xs text-center text-gray-600 italic">No active sessions</p>}
            </div>
        </div>
    );
}

export default SessionManager;
