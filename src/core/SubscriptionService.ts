/**
 * SubscriptionService - Handles communication with the secure PHP backend
 */
export interface SubscriptionStatus {
    success: boolean;
    tier: 'free' | 'starter' | 'pro';
    status: 'active' | 'cancelled' | 'expired' | 'pending';
    limits: {
        bots: number;
        messages_per_month: number;
        faqs: number;
        avatars: boolean;
        lead_capture: boolean;
        export: boolean;
        custom_prompt: boolean;
        own_api_key?: boolean;
    };
    usage: {
        messages_used: number;
        messages_limit: number;
        messages_remaining: number;
    };
    error?: string;
}

export class SubscriptionService {
    private static API_BASE = 'https://pay.memorykeep.cloud/api';

    static async checkStatus(token: string): Promise<SubscriptionStatus | null> {
        try {
            // SECURITY: Only use Authorization header, never pass token in URL
            const response = await fetch(`${this.API_BASE}/status.php`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                if (response.status === 401) {
                    localStorage.removeItem('mk_auth_token');
                }
                return { success: false, ...errorData } as any;
            }

            return await response.json();
        } catch (error) {
            console.error('Subscription check failed:', error);
            return null;
        }
    }

    static async logMessageUsage(token: string): Promise<boolean> {
        try {
            // SECURITY: Only use Authorization header and POST body, never URL params
            const response = await fetch(`${this.API_BASE}/use-message.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token })
            });

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Message usage logging failed:', error);
            return false;
        }
    }

    static getToken(): string | null {
        // Check URL params first
        const params = new URLSearchParams(window.location.search);
        const urlToken = params.get('token');
        if (urlToken) {
            localStorage.setItem('mk_auth_token', urlToken);
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
            return urlToken;
        }
        return localStorage.getItem('mk_auth_token');
    }

    static saveToken(token: string) {
        localStorage.setItem('mk_auth_token', token);
    }

    static logout() {
        // Clear auth token
        localStorage.removeItem('mk_auth_token');

        // Clear any cached subscription or user data
        sessionStorage.clear();

        // Reload to show login modal with clean state
        window.location.reload();
    }
}
