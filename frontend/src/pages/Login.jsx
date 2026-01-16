import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { auth } from '../services/api';
import { useAuthStore } from '../store/auth';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const login = useAuthStore(state => state.login);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    
    try {
      const { data } = await auth.login(email, password);
      
      if (data.success) {
        const user = data.data.user;
        const userType = user.role || user.user_type;
        const role = userType === 'admin' ? 'super-admin' : userType;
        const userWithRole = { ...user, role };
        
        login(userWithRole, data.data.token);
        navigate(`/${role}`);
      } else {
        setError(data.message || 'Login failed');
      }
    } catch (err) {
      setError('Invalid email or password');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 p-4">
      <div className="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="inline-block p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mb-4">
            <span className="text-4xl">🎓</span>
          </div>
          <h1 className="text-3xl font-bold text-gray-800 mb-2">PVGS ERP</h1>
          <p className="text-gray-600">College Management System</p>
        </div>
        
        {/* Error Alert */}
        {error && (
          <div className="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6 flex items-start">
            <span className="text-xl mr-2">⚠️</span>
            <span>{error}</span>
          </div>
        )}
        
        {/* Login Form */}
        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label className="block text-gray-700 text-sm font-semibold mb-2">
              📧 Email Address
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              placeholder="user@pvgs.edu"
              required
            />
          </div>
          
          <div>
            <label className="block text-gray-700 text-sm font-semibold mb-2">
              🔒 Password
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              placeholder="Enter your password"
              required
            />
          </div>
          
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition transform hover:scale-[1.02] active:scale-[0.98]"
          >
            {loading ? (
              <span className="flex items-center justify-center">
                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Logging in...
              </span>
            ) : (
              'Login to Portal →'
            )}
          </button>
        </form>
        
        {/* Test Accounts */}
        <div className="mt-8 bg-gradient-to-br from-blue-50 to-purple-50 p-4 rounded-lg border border-blue-100">
          <p className="font-semibold text-gray-700 mb-3 flex items-center">
            <span className="text-lg mr-2">👥</span>
            Demo Accounts
          </p>
          <div className="space-y-2 text-sm">
            <div className="flex items-center justify-between bg-white px-3 py-2 rounded">
              <span className="text-gray-700">👨‍💼 Principal</span>
              <code className="text-xs bg-gray-100 px-2 py-1 rounded">principal@pvgs.edu</code>
            </div>
            <div className="flex items-center justify-between bg-white px-3 py-2 rounded">
              <span className="text-gray-700">👨‍🏫 Faculty</span>
              <code className="text-xs bg-gray-100 px-2 py-1 rounded">faculty1@pvgs.edu</code>
            </div>
            <div className="flex items-center justify-between bg-white px-3 py-2 rounded">
              <span className="text-gray-700">👨‍🎓 Student</span>
              <code className="text-xs bg-gray-100 px-2 py-1 rounded">student1@pvgs.edu</code>
            </div>
          </div>
          <p className="mt-3 text-xs text-gray-600 text-center">
            🔑 Password for all: <code className="bg-white px-2 py-1 rounded font-mono">password123</code>
          </p>
        </div>

        {/* Back to Home */}
        <div className="mt-6 text-center">
          <a href="/" className="text-sm text-gray-600 hover:text-blue-600 transition">
            ← Back to Homepage
          </a>
        </div>
      </div>
    </div>
  );
}
