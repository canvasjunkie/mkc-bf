import React, { useState } from 'react';
import { Bot, BotModule } from '../types/Bot';
import { ModuleLibrary } from './ModuleLibrary';
import { ModuleCanvas } from './ModuleCanvas';
import { WidgetCustomizer } from './WidgetCustomizer';
import { Palette, Puzzle, Settings, Key, Copy, Check } from 'lucide-react';

interface BotBuilderProps {
  bot: Bot;
  onBotUpdate: (bot: Bot) => void;
  subscription: any;
}

export const BotBuilder: React.FC<BotBuilderProps> = ({ bot, onBotUpdate, subscription }) => {
  const [activeTab, setActiveTab] = useState<'modules' | 'widget' | 'settings' | 'api'>('modules');
  const [deployUrl, setDeployUrl] = useState('');
  const [copied, setCopied] = useState(false);

  const generateEmbedCode = (url: string) => {
    // Add embed parameter to hide the widget's internal bubble
    const embedUrl = url.includes('?') ? `${url}&embed=true` : `${url}?embed=true`;
    return `<script>
(function() {
  var btn = document.createElement('div');
  btn.innerHTML = '💬';
  btn.style.cssText = 'position:fixed;bottom:20px;right:20px;width:60px;height:60px;background:${bot.widget.theme.primaryColor};border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:24px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9998;';
  
  var iframe = document.createElement('iframe');
  iframe.src = '${embedUrl}';
  iframe.style.cssText = 'position:fixed;bottom:90px;right:20px;width:380px;height:520px;border:none;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.12);z-index:9999;display:none;background:white;';
  
  btn.onclick = function() {
    iframe.style.display = iframe.style.display === 'none' ? 'block' : 'none';
  };
  
  document.body.appendChild(btn);
  document.body.appendChild(iframe);
})();
</script>`;
  };

  const copyEmbedCode = async () => {
    if (!deployUrl) return;
    await navigator.clipboard.writeText(generateEmbedCode(deployUrl));
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handleModuleAdd = (moduleType: string) => {
    const newModule: BotModule = {
      id: Date.now().toString(),
      type: moduleType,
      config: {},
      position: { x: 100, y: 100 },
      enabled: true
    };

    const updatedBot = {
      ...bot,
      modules: [...bot.modules, newModule]
    };

    onBotUpdate(updatedBot);
  };

  const handleModuleUpdate = (moduleId: string, updates: Partial<BotModule>) => {
    const updatedBot = {
      ...bot,
      modules: bot.modules.map(module =>
        module.id === moduleId ? { ...module, ...updates } : module
      )
    };
    onBotUpdate(updatedBot);
  };

  const handleModuleDelete = (moduleId: string) => {
    const updatedBot = {
      ...bot,
      modules: bot.modules.filter(module => module.id !== moduleId)
    };

    onBotUpdate(updatedBot);
  };

  const handleWidgetUpdate = (widgetConfig: Bot['widget']) => {
    const updatedBot = {
      ...bot,
      widget: widgetConfig
    };

    onBotUpdate(updatedBot);
  };

  const handleBotSettingsUpdate = (updates: Partial<Bot>) => {
    const updatedBot = {
      ...bot,
      ...updates
    };

    onBotUpdate(updatedBot);
  };

  const handleApiSettingsUpdate = (settings: Bot['settings']) => {
    const updatedBot = {
      ...bot,
      settings
    };

    onBotUpdate(updatedBot);
  };

  const tabs = [
    { id: 'modules', label: 'Modules', icon: Puzzle },
    { id: 'widget', label: 'Chat Widget', icon: Palette },
    { id: 'api', label: 'AI Settings', icon: Key },
    { id: 'settings', label: 'Bot Settings', icon: Settings }
  ];

  const handleSave = () => {
    onBotUpdate(bot);
  };

  return (
    <div className="flex-1 flex flex-col overflow-hidden">
      {/* Tab Navigation */}
      <div className="bg-white border-b">
        <div className="flex space-x-1 px-3 sm:px-6 py-3 overflow-x-auto scrollbar-hide">
          {tabs.map((tab) => {
            const Icon = tab.icon;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as any)}
                className={`flex items-center space-x-1.5 sm:space-x-2 px-3 sm:px-4 py-2 text-sm font-medium rounded-md transition-colors whitespace-nowrap ${activeTab === tab.id
                  ? 'bg-indigo-100 text-indigo-700'
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                  }`}
              >
                <Icon className="w-4 h-4" />
                <span className="hidden sm:inline">{tab.label}</span>
                <span className="sm:hidden">{tab.label.split(' ')[0]}</span>
              </button>
            );
          })}
        </div>
        <div className="p-3 sm:p-4 border-b">
          <button
            onClick={handleSave}
            className="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Save Bot
          </button>
        </div>
      </div>

      {/* Tab Content */}
      <div className="flex-1 flex flex-col overflow-y-auto">
        {activeTab === 'modules' && (
          <div className="flex flex-1">
            <ModuleLibrary onModuleAdd={handleModuleAdd} subscription={subscription} />
            <ModuleCanvas
              modules={bot.modules}
              onModuleUpdate={handleModuleUpdate}
              onModuleDelete={handleModuleDelete}
              subscription={subscription}
            />
          </div>
        )}

        {activeTab === 'widget' && (
          <div className="flex-1 overflow-y-auto">
            <WidgetCustomizer
              bot={bot}
              onWidgetUpdate={handleWidgetUpdate}
              subscription={subscription}
            />
          </div>
        )}

        {activeTab === 'api' && (
          <div className="flex-1 overflow-y-auto p-6">
            <div className="max-w-2xl mx-auto">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">AI & API Settings</h2>

              <div className="space-y-6">
                {/* Free Tier Info Banner */}
                {subscription && subscription.tier === 'free' && (
                  <div className="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-4">
                    <h4 className="font-medium text-indigo-900 mb-2">🎁 Free Tier AI</h4>
                    <p className="text-sm text-indigo-800">
                      Your bot uses our shared Gemini AI — no API key needed!
                      Upgrade to <strong>Pro</strong> to use OpenRouter with access to GPT-4, Claude, and more models.
                    </p>
                  </div>
                )}

                {/* AI Provider Selection - Only show for Pro users with own_api_key permission */}
                {subscription?.limits?.own_api_key && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      AI Provider
                    </label>
                    <select
                      value={bot.settings.aiProvider || 'gemini'}
                      onChange={(e) => handleApiSettingsUpdate({
                        ...bot.settings,
                        aiProvider: e.target.value as 'openrouter' | 'gemini',
                      })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="gemini">Google Gemini (Included)</option>
                      <option value="openrouter">OpenRouter (Your API Key)</option>
                    </select>
                    <p className="text-xs text-gray-500 mt-1">
                      Pro users can bring their own OpenRouter API key for access to GPT-4, Claude, Llama, and more.
                    </p>
                  </div>
                )}

                {/* Gemini Settings - Simple info panel (model is hardcoded on backend) */}
                {(bot.settings.aiProvider === 'gemini' || !subscription?.limits?.own_api_key) && (
                  <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
                    <h4 className="font-medium text-blue-900">🔮 Gemini AI</h4>
                    <p className="text-sm text-blue-800">
                      Your bot uses <strong>Gemini 2.0 Flash</strong> — fast, smart, and cost-efficient.
                    </p>
                    <p className="text-xs text-blue-600">
                      ✓ No API key required &nbsp;•&nbsp; ✓ Managed by MemoryKeep &nbsp;•&nbsp; ✓ Automatic updates
                    </p>
                  </div>
                )}

                {/* OpenRouter Settings - Pro Only */}
                {subscription?.limits?.own_api_key && bot.settings.aiProvider === 'openrouter' && (
                  <div className="bg-purple-50 border border-purple-200 rounded-lg p-4 space-y-4">
                    <h4 className="font-medium text-purple-900">🌐 OpenRouter Configuration</h4>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        OpenRouter API Key
                      </label>
                      <input
                        type="password"
                        value={bot.settings.openRouterApiKey || ''}
                        onChange={(e) => handleApiSettingsUpdate({
                          ...bot.settings,
                          openRouterApiKey: e.target.value
                        })}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="sk-or-v1-..."
                      />
                      <p className="text-sm text-gray-500 mt-1">
                        Get your API key from <a href="https://openrouter.ai" target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-800">OpenRouter.ai</a>
                      </p>
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Model
                      </label>
                      <input
                        type="text"
                        value={bot.settings.model || ''}
                        onChange={(e) => handleApiSettingsUpdate({
                          ...bot.settings,
                          model: e.target.value
                        })}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="anthropic/claude-3-haiku"
                      />
                      <p className="text-sm text-gray-500 mt-1">
                        Enter any model from <a href="https://openrouter.ai/models" target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-800">OpenRouter Models</a>
                      </p>
                    </div>
                  </div>
                )}

                {/* System Prompt Section */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">
                    <span>System Prompt</span>
                    {subscription && !subscription.limits.custom_prompt && (
                      <span className="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Premium</span>
                    )}
                  </label>
                  {subscription && !subscription.limits.custom_prompt ? (
                    // Free tier - show the default prompt as read-only with nice styling
                    <div className="bg-gray-50 border border-gray-200 rounded-md p-4">
                      <p className="text-sm text-gray-700 leading-relaxed">
                        <strong>Default MemoryKeep Assistant Prompt:</strong>
                      </p>
                      <p className="text-sm text-gray-600 mt-2 italic">
                        "You are a friendly, professional AI assistant. Your role is to help website visitors by answering their questions clearly and concisely. Be helpful, polite, and guide users toward the information or services they need. If you don't know something, be honest about it and suggest they contact the business directly. Keep responses brief but informative."
                      </p>
                      <p className="text-xs text-indigo-600 mt-3">
                        ⬆️ Upgrade to Starter or Pro to customize your bot's personality!
                      </p>
                    </div>
                  ) : (
                    // Starter/Pro - editable prompt
                    <textarea
                      value={bot.settings.systemPrompt || ''}
                      onChange={(e) => handleApiSettingsUpdate({
                        ...bot.settings,
                        systemPrompt: e.target.value
                      })}
                      rows={4}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="You are a helpful assistant for [Your Business]. Be friendly, answer questions about our products/services, and help visitors find what they need..."
                    />
                  )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Temperature
                    </label>
                    <input
                      type="number"
                      min="0"
                      max="2"
                      step="0.1"
                      value={bot.settings.temperature || 0.7}
                      onChange={(e) => handleApiSettingsUpdate({
                        ...bot.settings,
                        temperature: parseFloat(e.target.value)
                      })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Max Tokens
                    </label>
                    <input
                      type="number"
                      min="1"
                      max="4000"
                      value={bot.settings.maxTokens || 1000}
                      onChange={(e) => handleApiSettingsUpdate({
                        ...bot.settings,
                        maxTokens: parseInt(e.target.value)
                      })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'settings' && (
          <div className="flex-1 overflow-y-auto p-6">
            <div className="max-w-2xl mx-auto">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">Bot Settings</h2>

              <div className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Bot Name
                  </label>
                  <input
                    type="text"
                    value={bot.name}
                    onChange={(e) => handleBotSettingsUpdate({ name: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Description
                  </label>
                  <textarea
                    value={bot.description}
                    onChange={(e) => handleBotSettingsUpdate({ description: e.target.value })}
                    rows={3}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>

                <div className="bg-gray-50 rounded-lg p-4">
                  <h3 className="font-medium text-gray-900 mb-2">Bot Information</h3>
                  <div className="space-y-2 text-sm text-gray-600">
                    <div>Created: {new Date(bot.createdAt).toLocaleString()}</div>
                    <div>Last Updated: {new Date(bot.updatedAt).toLocaleString()}</div>
                    <div>Modules: {bot.modules.length}</div>
                    <div>Bot ID: {bot.id}</div>
                  </div>
                </div>

                {/* Embed Code Generator */}
                <div className="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                  <h3 className="font-medium text-indigo-900 mb-3">🔗 Embed Code Generator</h3>
                  <p className="text-sm text-indigo-800 mb-3">
                    Deploy your widget to Netlify, then paste the URL below to get embed code.
                  </p>
                  <div className="space-y-3">
                    <input
                      type="url"
                      value={deployUrl}
                      onChange={(e) => setDeployUrl(e.target.value)}
                      placeholder="https://bots.cincyweb.pro"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    {deployUrl && (
                      <>
                        <div className="bg-gray-900 text-green-400 p-3 rounded-md text-xs font-mono overflow-x-auto max-h-32 overflow-y-auto">
                          <pre>{generateEmbedCode(deployUrl)}</pre>
                        </div>
                        <button
                          onClick={copyEmbedCode}
                          className="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                        >
                          {copied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                          <span>{copied ? 'Copied!' : 'Copy Embed Code'}</span>
                        </button>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div >
  );
};