import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuthStore } from './store/auth';
import Login from './pages/Login';
import PrincipalDashboard from './pages/PrincipalDashboard';
import FacultyDashboard from './pages/FacultyDashboard';
import StudentDashboard from './pages/StudentDashboard';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, refetchOnWindowFocus: false }
  }
});

function ProtectedRoute({ children, allowedRoles }) {
  const user = useAuthStore(state => state.user);
  
  if (!user) return <Navigate to="/login" replace />;
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/login" replace />;
  }
  
  return children;
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter basename="/app">
        <Routes>
          <Route path="/login" element={<Login />} />
          
          <Route path="/principal" element={
            <ProtectedRoute allowedRoles={['principal', 'super-admin']}>
              <PrincipalDashboard />
            </ProtectedRoute>
          } />
          
          <Route path="/super-admin" element={
            <ProtectedRoute allowedRoles={['super-admin']}>
              <PrincipalDashboard />
            </ProtectedRoute>
          } />
          
          <Route path="/faculty" element={
            <ProtectedRoute allowedRoles={['faculty']}>
              <FacultyDashboard />
            </ProtectedRoute>
          } />
          
          <Route path="/student" element={
            <ProtectedRoute allowedRoles={['student']}>
              <StudentDashboard />
            </ProtectedRoute>
          } />
          
          <Route path="/" element={<Navigate to="/login" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
