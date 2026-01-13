declare module 'netlify-identity-widget' {
    export interface User {
        id: string;
        email: string;
        user_metadata: {
            full_name?: string;
            avatar_url?: string;
        };
        app_metadata: {
            provider?: string;
            roles?: string[];
        };
        created_at: string;
        confirmed_at?: string;
        jwt: () => Promise<string>;
    }

    interface NetlifyIdentity {
        init: (options?: { container?: string; locale?: string }) => void;
        open: (tab?: 'login' | 'signup') => void;
        close: () => void;
        logout: () => Promise<void>;
        currentUser: () => User | null;
        on: (event: 'init' | 'login' | 'logout' | 'signup' | 'error' | 'open' | 'close', callback: (user?: User) => void) => void;
        off: (event: 'init' | 'login' | 'logout' | 'signup' | 'error' | 'open' | 'close') => void;
    }

    const netlifyIdentity: NetlifyIdentity;
    export default netlifyIdentity;
}
