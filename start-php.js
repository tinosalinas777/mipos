const { spawn } = require("child_process");
const path = require("path");
const { app } = require("electron");

function basePath() {
  return app.isPackaged ? process.resourcesPath : __dirname;
}

const base = basePath();

const phpPath = path.join(base, "php", "php.exe");
const publicPath = path.join(base, "puntodeventa", "public");
const routerPath = path.join(publicPath, "router.php");

console.log("PHP Path:", phpPath);
console.log("Public Path:", publicPath);
console.log("==== DEBUG PATHS ====");
console.log("isPackaged:", app.isPackaged);
console.log("Base path:", base);
console.log("PHP path:", phpPath);
console.log("Public path:", publicPath);
console.log("Router:", routerPath);
console.log("======================");

// Ejecutar PHP embebido
const php = spawn(phpPath, [
  "-S",
  "127.0.0.1:8000",
  "-t",
  publicPath,
  routerPath,
]);

php.stdout.on("data", (data) => console.log(`PHP: ${data}`));
php.stderr.on("data", (data) => console.error(`PHP ERROR: ${data}`));
php.on("close", (code) => console.log(`PHP exited with code ${code}`));
