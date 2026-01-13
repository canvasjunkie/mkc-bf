import React from 'react';
import { moduleRegistry } from '../modules/moduleRegistry';
import { Plus, Key } from 'lucide-react';

interface ModuleLibraryProps {
  onModuleAdd: (moduleType: string) => void;
  subscription: any;
}

export const ModuleLibrary: React.FC<ModuleLibraryProps> = ({ onModuleAdd, subscription }) => {
  const modules = moduleRegistry.getAllModules();

  const isModuleLocked = (moduleType: string) => {
    if (!subscription) return false;
    if (moduleType === 'lead-capture') {
      return !subscription.limits.lead_capture;
    }
    return false;
  };

  return (
    <div className="w-80 bg-white shadow-lg border-r">
      <div className="p-4 border-b">
        <h2 className="text-lg font-semibold text-gray-900">Module Library</h2>
        <p className="text-sm text-gray-500 mt-1">Drag modules to the canvas or click to add</p>
      </div>

      <div className="p-4 space-y-3">
        {modules.map((module) => {
          const locked = isModuleLocked(module.type);
          return (
            <div
              key={module.type}
              className={`group p-4 rounded-lg border transition-all ${locked
                ? 'bg-gray-100 border-gray-200 cursor-not-allowed opacity-75'
                : 'bg-gray-50 border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 cursor-pointer'
                }`}
              onClick={() => !locked && onModuleAdd(module.type)}
            >
              <div className="flex items-start justify-between">
                <div className="flex items-center space-x-3">
                  <div className="text-2xl">{module.icon}</div>
                  <div>
                    <h3 className="font-medium text-gray-900 flex items-center">
                      {module.name}
                      {locked && <span className="ml-2 text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full uppercase tracking-wider font-bold">Premium</span>}
                    </h3>
                    <p className="text-sm text-gray-500">{module.description}</p>
                  </div>
                </div>
                {locked ? (
                  <Key className="w-5 h-5 text-gray-400" />
                ) : (
                  <Plus className="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};