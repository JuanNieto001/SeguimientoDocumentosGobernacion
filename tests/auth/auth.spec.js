import { test, expect } from '@playwright/test';
import { LoginHelper } from '../helpers/login.helper.js';

/**
 * PRUEBAS DE AUTENTICACIÓN
 * Basado en Cypress: AUTH-001 a AUTH-011
 */

test.describe('Módulo Autenticación', () => {
  
  test('AUTH-001: Login exitoso con credenciales válidas', async ({ page }) => {
    const login = new LoginHelper(page);
    
    console.log('✅ Paso 1: Ir a login');
    await page.goto('/login');
    
    console.log('✅ Paso 2: Ingresar credenciales REALES');
    await page.fill('input[name="email"]', 'admin@demo.com');
    await page.fill('input[name="password"]', '12345');
    
    console.log('✅ Paso 3: Hacer clic en Iniciar Sesión');
    await page.click('button[type="submit"]');
    
    console.log('✅ Paso 4: Verificar redirección a dashboard');
    await expect(page).toHaveURL(/.*dashboard/);
  });

  test('AUTH-002: Login fallido con email incorrecto', async ({ page }) => {
    try {
      await page.goto('/login');
      await page.fill('input[name="email"]', 'noexiste@demo.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    
    // Verificar que sigue en login
    await expect(page).toHaveURL(/.*login/);
    await expect(page.locator('body')).toContainText(/credenciales|error/i);
  });

  test('AUTH-003: Login fallido con contraseña incorrecta', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@test.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/.*login/);
    await expect(page.locator('body')).toContainText(/credenciales|error/i);
  });

  test('AUTH-004: Login con campos vacíos', async ({ page }) => {
    await page.goto('/login');
    await page.click('button[type="submit"]');
    
    // Verificar validación HTML5 o mensajes de error
    await expect(page).toHaveURL(/.*login/);
  });

  test('AUTH-005: Logout exitoso', async ({ page }) => {
    const login = new LoginHelper(page);
    
    // Login primero
    await login.loginAsAdmin();
    
    // Hacer logout
    await login.logout();
    
    // Verificar redirección a login
    await expect(page).toHaveURL(/.*login/);
  });

  test('AUTH-006: Acceso sin autenticación', async ({ page }) => {
    await page.goto('/dashboard');
    
    // Debe redirigir a login
    await expect(page).toHaveURL(/.*login/);
  });
});
