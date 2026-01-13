import React from 'react';
import { useAuth } from '../context/AuthContext';
import mkLogo from '../assets/logo-nobg.png';
import bfLogo from '../assets/BF-nobg.png';

export function LoginPage() {
    const { login, signup, isLoading } = useAuth();

    if (isLoading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex flex-col">
            {/* Header */}
            <header className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center py-4">
                        <div className="flex items-center space-x-3">
                            <img src={mkLogo} alt="MemoryKeep" className="h-10 w-auto" />
                            <img src={bfLogo} alt="Bot Factory" className="h-10 w-auto" />
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <div className="flex-1 flex items-center justify-center px-4">
                <div className="max-w-md w-full">
                    <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                        {/* Logo Section */}
                        <div className="text-center mb-8">
                            <div className="flex items-center justify-center space-x-3 mb-4">
                                <img src={mkLogo} alt="MemoryKeep" className="h-16 w-auto" />
                                <img src={bfLogo} alt="Bot Factory" className="h-16 w-auto" />
                            </div>
                            <h1 className="text-2xl font-bold text-gray-900 mb-2">
                                Welcome to Bot Factory
                            </h1>
                            <p className="text-gray-600">
                                Build AI-powered chatbots in minutes
                            </p>
                        </div>

                        {/* Features List */}
                        <div className="mb-8 space-y-3">
                            <div className="flex items-center text-sm text-gray-600">
                                <span className="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-green-600">✓</span>
                                </span>
                                Create custom AI chatbots
                            </div>
                            <div className="flex items-center text-sm text-gray-600">
                                <span className="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-green-600">✓</span>
                                </span>
                                Export & embed anywhere
                            </div>
                            <div className="flex items-center text-sm text-gray-600">
                                <span className="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-green-600">✓</span>
                                </span>
                                Capture leads automatically
                            </div>
                        </div>

                        {/* Auth Buttons */}
                        <div className="space-y-3">
                            <button
                                onClick={login}
                                className="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Log In
                            </button>
                            <button
                                onClick={signup}
                                className="w-full py-3 px-4 bg-white hover:bg-gray-50 text-indigo-600 font-semibold rounded-lg border-2 border-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Create Account
                            </button>
                        </div>

                        {/* Footer */}
                        <p className="mt-6 text-center text-xs text-gray-500">
                            By signing up, you agree to our Terms of Service
                        </p>
                    </div>

                    {/* Pricing hint */}
                    <p className="mt-6 text-center text-sm text-gray-600">
                        Start free • Upgrade anytime
                    </p>
                </div>
            </div>
        </div>
    );
}
