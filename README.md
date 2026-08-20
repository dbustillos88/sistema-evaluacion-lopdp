cd C:\xampp\htdocs\sistema_evaluacion_lopdp

echo "# Sistema de Evaluación de Cumplimiento LOPDP" > README.md
echo "" >> README.md
echo "## 📊 Sistema web para evaluar el cumplimiento de la Ley Orgánica de Protección de Datos Personales" >> README.md
echo "" >> README.md
echo "### 🚀 Tecnologías Utilizadas" >> README.md
echo "- PHP 7.4+" >> README.md
echo "- MySQL 5.7+" >> README.md
echo "- HTML5, CSS3, JavaScript" >> README.md
echo "" >> README.md
echo "### 📂 Estructura del Proyecto" >> README.md
echo "" >> README.md
echo "\`\`\`" >> README.md
echo "sistema_evaluacion_lopdp/" >> README.md
echo "├── config/" >> README.md
echo "│   └── conexion.php" >> README.md
echo "├── css/" >> README.md
echo "│   └── estilos.css" >> README.md
echo "├── js/" >> README.md
echo "│   └── funciones.js" >> README.md
echo "├── sql/" >> README.md
echo "│   └── estructura_bd.sql" >> README.md
echo "├── dashboard.php" >> README.md
echo "├── generar_reporte.php" >> README.md
echo "├── guardar_evaluacion.php" >> README.md
echo "└── index.php" >> README.md
echo "\`\`\`" >> README.md

git add README.md
git commit -m "docs: agregar README.md"
git push origin main