// Custom Reporter para generar documentación de evidencias
class CertificationReporter {
  constructor(options) {
    this.results = [];
    this.startTime = Date.now();
  }

  onBegin(config, suite) {
    console.log(`\n🎯 Iniciando certificación QA - ${suite.allTests().length} tests\n`);
  }

  onTestEnd(test, result) {
    const testInfo = {
      id: this.generateTestId(test),
      nombre: test.title,
      modulo: this.extractModule(test),
      estado: result.status === 'passed' ? '✅ APROBADO' : '❌ FALLIDO',
      duracion: `${(result.duration / 1000).toFixed(2)}s`,
      evidencias: this.getEvidencePaths(test, result),
      error: result.error ? result.error.message : null,
      timestamp: new Date().toISOString()
    };

    this.results.push(testInfo);

    // Mostrar resultado en consola
    const status = result.status === 'passed' ? '✅' : '❌';
    console.log(`${status} ${testInfo.id}: ${testInfo.nombre} (${testInfo.duracion})`);
  }

  onEnd() {
    const duration = ((Date.now() - this.startTime) / 1000).toFixed(2);
    const passed = this.results.filter(r => r.estado.includes('APROBADO')).length;
    const failed = this.results.filter(r => r.estado.includes('FALLIDO')).length;
    const total = this.results.length;

    console.log(`\n========================================`);
    console.log(`🎉 CERTIFICACIÓN COMPLETADA`);
    console.log(`========================================`);
    console.log(`Total: ${total} tests`);
    console.log(`✅ Aprobados: ${passed} (${((passed/total)*100).toFixed(1)}%)`);
    console.log(`❌ Fallidos: ${failed}`);
    console.log(`⏱️  Duración: ${duration}s`);
    console.log(`========================================\n`);

    // Generar archivo de resultados
    this.generateResultsFile();
  }

  generateTestId(test) {
    const title = test.title;
    // Extraer ID del título (ej: "WKFL-001: ...")
    const match = title.match(/^([A-Z]+-\d+)/);
    return match ? match[1] : `TEST-${Math.random().toString(36).substr(2, 9)}`;
  }

  extractModule(test) {
    const file = test.location.file;
    if (file.includes('auth')) return 'Autenticación';
    if (file.includes('users')) return 'Usuarios';
    if (file.includes('procesos')) return 'Procesos';
    if (file.includes('workflow')) return 'Workflow CD-PN';
    if (file.includes('documents')) return 'Documentos';
    if (file.includes('dashboard')) return 'Dashboard';
    if (file.includes('api')) return 'API';
    if (file.includes('responsive')) return 'Responsive';
    if (file.includes('motor-flujos')) return 'Motor Flujos';
    return 'Otros';
  }

  getEvidencePaths(test, result) {
    const paths = [];
    
    if (result.attachments) {
      result.attachments.forEach(attachment => {
        if (attachment.name === 'screenshot') {
          paths.push(`Screenshot: ${attachment.path}`);
        }
        if (attachment.name === 'video') {
          paths.push(`Video: ${attachment.path}`);
        }
        if (attachment.name === 'trace') {
          paths.push(`Trace: ${attachment.path}`);
        }
      });
    }

    return paths.length > 0 ? paths.join(', ') : 'N/A';
  }

  generateResultsFile() {
    const fs = require('fs');
    const path = require('path');

    // Generar CSV para Excel
    const csvContent = this.generateCSV();
    fs.writeFileSync(path.join('test-results', 'resultados-certificacion.csv'), csvContent);

    // Generar MD para documentación
    const mdContent = this.generateMarkdown();
    fs.writeFileSync(path.join('test-results', 'REPORTE_CERTIFICACION.md'), mdContent);

    console.log('📄 Reportes generados:');
    console.log('   - test-results/resultados-certificacion.csv (Excel)');
    console.log('   - test-results/REPORTE_CERTIFICACION.md (Markdown)');
  }

  generateCSV() {
    let csv = 'ID,Módulo,Caso de Prueba,Estado,Duración,Evidencias,Error,Timestamp\n';
    
    this.results.forEach(r => {
      csv += `"${r.id}","${r.modulo}","${r.nombre}","${r.estado}","${r.duracion}","${r.evidencias}","${r.error || ''}","${r.timestamp}"\n`;
    });

    return csv;
  }

  generateMarkdown() {
    const passed = this.results.filter(r => r.estado.includes('APROBADO')).length;
    const failed = this.results.filter(r => r.estado.includes('FALLIDO')).length;
    const total = this.results.length;

    let md = `# 📊 REPORTE DE CERTIFICACIÓN QA\n\n`;
    md += `**Fecha:** ${new Date().toLocaleString()}\n\n`;
    md += `## Resumen Ejecutivo\n\n`;
    md += `| Métrica | Valor |\n`;
    md += `|---------|-------|\n`;
    md += `| Total Tests | ${total} |\n`;
    md += `| ✅ Aprobados | ${passed} (${((passed/total)*100).toFixed(1)}%) |\n`;
    md += `| ❌ Fallidos | ${failed} |\n`;
    md += `| Cobertura | ${((passed/total)*100).toFixed(1)}% |\n\n`;

    md += `## Resultados Detallados\n\n`;
    md += `| ID | Módulo | Caso | Estado | Duración |\n`;
    md += `|----|--------|------|--------|----------|\n`;

    this.results.forEach(r => {
      md += `| ${r.id} | ${r.modulo} | ${r.nombre} | ${r.estado} | ${r.duracion} |\n`;
    });

    if (failed > 0) {
      md += `\n## ❌ Tests Fallidos\n\n`;
      this.results.filter(r => r.estado.includes('FALLIDO')).forEach(r => {
        md += `### ${r.id}: ${r.nombre}\n`;
        md += `**Error:** ${r.error}\n\n`;
      });
    }

    md += `\n## 📂 Ubicación de Evidencias\n\n`;
    md += `- **Screenshots:** \`test-results/\` (por test)\n`;
    md += `- **Videos:** \`test-results/\` (por test)\n`;
    md += `- **Traces:** \`test-results/\` (por test)\n`;
    md += `- **Reporte HTML:** \`playwright-report/index.html\`\n`;

    return md;
  }
}

// ES Module export
export default CertificationReporter;
