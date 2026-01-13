import React, { useState } from 'react';
import { Key, ExternalLink, ArrowRight, Loader2 } from 'lucide-react';
import { SubscriptionService } from '../core/SubscriptionService';

interface ApiKeyModalProps {
    onSuccess: (token: string) => void;
}

export const ApiKeyModal: React.FC<ApiKeyModalProps> = ({ onSuccess }) => {
    const [token, setToken] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!token.trim()) return;

        setLoading(true);
        setError('');

        const status = await SubscriptionService.checkStatus(token.trim());

        if (status && status.success) {
            SubscriptionService.saveToken(token.trim());
            onSuccess(token.trim());
        } else {
            setError(status?.error || 'Invalid API Token. Please check and try again.');
        }
        setLoading(false);
    };

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-300">
                <div className="bg-indigo-600 p-8 text-center relative">
                    <div className="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/30">
                        <Key className="w-8 h-8 text-white" />
                    </div>
                    <h2 className="text-2xl font-bold text-white mb-2">Connect to Bot Factory</h2>
                    <p className="text-indigo-100 text-sm">Please enter your API Auth Token to continue</p>

                    <div className="absolute top-0 right-0 p-4">
                        <a
                            href="https://pay.memorykeep.cloud/dashboard.php"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-white/60 hover:text-white transition-colors"
                            title="Go to Dashboard"
                        >
                            <ExternalLink className="w-5 h-5" />
                        </a>
                    </div>
                </div>

                <div className="p-8">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="token" className="block text-sm font-medium text-gray-700 mb-2">
                                API Auth Token
                            </label>
                            <input
                                id="token"
                                type="password"
                                value={token}
                                onChange={(e) => setToken(e.target.value)}
                                placeholder="Paste your token from the dashboard..."
                                className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-gray-400"
                                required
                            />
                            {error && (
                                <p className="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <span className="w-1 h-1 bg-red-600 rounded-full inline-block" />
                                    {error}
                                </p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={loading || !token.trim()}
                            className="w-full bg-indigo-600 text-white font-semibold py-4 rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 group"
                        >
                            {loading ? (
                                <Loader2 className="w-5 h-5 animate-spin" />
                            ) : (
                                <>
                                    Connect Factory
                                    <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                </>
                            )}
                        </button>

                        <div className="pt-4 border-t border-gray-100 text-center">
                            <p className="text-sm text-gray-500">
                                Don't have a token?{' '}
                                <a
                                    href="https://pay.memorykeep.cloud/signup.php"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-indigo-600 font-semibold hover:text-indigo-700 inline-flex items-center gap-1"
                                >
                                    Sign up for an account
                                    <ExternalLink className="w-3 h-3" />
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};
