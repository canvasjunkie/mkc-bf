import { createContext, useContext, useState, useEffect, ReactNode } from 'react';

// PHP Backend URLs
const AUTH_BASE_URL = 'https://pay.memorykeep.cloud';

interface User {
    id: string;
    email: string;
    tier: string;
}

interface AuthContextType {
    user: User | null;
    isLoading: boolean;
    login: () => void;
    logout: () => void;
    signup: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        checkSession();
    }, []);

    const checkSession = async () => {
        try {
            const response = await fetch(`${AUTH_BASE_URL}/api/status.php`, {
                credentials: 'include'
            });
            if (response.ok) {
                const data = await response.json();
                if (data.authenticated && data.user) {
                    setUser(data.user);
                }
            }
        } catch (error) {
            console.log('Not authenticated or API unavailable');
        } finally {
            setIsLoading(false);
        }
    };

    const login = () => {
        window.location.href = `${AUTH_BASE_URL}/login.php`;
    };

    const logout = () => {
        window.location.href = `${AUTH_BASE_URL}/logout.php`;
    };

    const signup = () => {
        window.location.href = `${AUTH_BASE_URL}/signup.php`;
    };

    return (
        <AuthContext.Provider value={{ user, isLoading, login, logout, signup }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}

export function getUserId(): string | null {
    return null;
}

export async function getAuthToken(): Promise<string | null> {
    return null;
}
