import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { MainLayout } from '../layouts/MainLayout/MainLayout'
import { LoginPage } from '../pages/auth/Login'
import { HomePage } from '../pages/home/Home'
import { AuthProvider } from './providers/AuthProvider'
import { ThemeProvider } from './providers/ThemeProvider'
import { RequireAuth } from './routes/RequireAuth'

export function AppRouter() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route
              path="/"
              element={
                <RequireAuth>
                  <MainLayout />
                </RequireAuth>
              }
            >
              <Route index element={<HomePage />} />
            </Route>
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  )
}
