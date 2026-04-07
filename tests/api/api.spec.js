import { test, expect } from '@playwright/test';

/**
 * PRUEBAS DE API ENDPOINTS
 * Casos: API-001, API-002
 */

test.describe.skip('Módulo API Endpoints', () => {
  // ⚠️ TESTS DESHABILITADOS - API tests requieren configuración
  
  test('API-001: Endpoint de autenticación', async ({ request }) => {
    console.log('✅ Hacer request a API de login');
    
    const response = await request.post('/api/login', {
      data: {
        email: 'admin@test.com',
        password: 'Test1234!'
      }
    });
    
    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    
    // Verificar que devuelve token o datos de usuario
    expect(body).toHaveProperty('token');
    console.log('✅ Token recibido:', body.token ? 'Sí' : 'No');
  });

  test('API-002: Endpoint sin autenticación', async ({ request }) => {
    console.log('✅ Intentar acceder a endpoint protegido sin token');
    
    const response = await request.get('/api/usuarios');
    
    // Debe retornar 401 Unauthorized
    expect(response.status()).toBe(401);
    console.log(`✅ Status correcto: ${response.status()}`);
  });

  test('API-003: Endpoint con autenticación válida', async ({ request }) => {
    // Primero obtener token
    const loginResponse = await request.post('/api/login', {
      data: {
        email: 'admin@test.com',
        password: 'Test1234!'
      }
    });
    
    const { token } = await loginResponse.json();
    
    // Hacer request con token
    const response = await request.get('/api/usuarios', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(Array.isArray(body) || body.data).toBeTruthy();
  });
});
