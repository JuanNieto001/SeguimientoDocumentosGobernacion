# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: api\api.spec.js >> Módulo API Endpoints >> API-003: Endpoint con autenticación válida
- Location: tests\api\api.spec.js:38:3

# Error details

```
SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | /**
  4  |  * PRUEBAS DE API ENDPOINTS
  5  |  * Casos: API-001, API-002
  6  |  */
  7  | 
  8  | test.describe('Módulo API Endpoints', () => {
  9  |   
  10 |   test('API-001: Endpoint de autenticación', async ({ request }) => {
  11 |     console.log('✅ Hacer request a API de login');
  12 |     
  13 |     const response = await request.post('/api/login', {
  14 |       data: {
  15 |         email: 'admin@test.com',
  16 |         password: 'Test1234!'
  17 |       }
  18 |     });
  19 |     
  20 |     expect(response.ok()).toBeTruthy();
  21 |     const body = await response.json();
  22 |     
  23 |     // Verificar que devuelve token o datos de usuario
  24 |     expect(body).toHaveProperty('token');
  25 |     console.log('✅ Token recibido:', body.token ? 'Sí' : 'No');
  26 |   });
  27 | 
  28 |   test('API-002: Endpoint sin autenticación', async ({ request }) => {
  29 |     console.log('✅ Intentar acceder a endpoint protegido sin token');
  30 |     
  31 |     const response = await request.get('/api/usuarios');
  32 |     
  33 |     // Debe retornar 401 Unauthorized
  34 |     expect(response.status()).toBe(401);
  35 |     console.log(`✅ Status correcto: ${response.status()}`);
  36 |   });
  37 | 
  38 |   test('API-003: Endpoint con autenticación válida', async ({ request }) => {
  39 |     // Primero obtener token
  40 |     const loginResponse = await request.post('/api/login', {
  41 |       data: {
  42 |         email: 'admin@test.com',
  43 |         password: 'Test1234!'
  44 |       }
  45 |     });
  46 |     
> 47 |     const { token } = await loginResponse.json();
     |                       ^ SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
  48 |     
  49 |     // Hacer request con token
  50 |     const response = await request.get('/api/usuarios', {
  51 |       headers: {
  52 |         'Authorization': `Bearer ${token}`
  53 |       }
  54 |     });
  55 |     
  56 |     expect(response.ok()).toBeTruthy();
  57 |     const body = await response.json();
  58 |     expect(Array.isArray(body) || body.data).toBeTruthy();
  59 |   });
  60 | });
  61 | 
```