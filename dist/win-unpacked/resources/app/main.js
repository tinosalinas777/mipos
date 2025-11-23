const { app, BrowserWindow } = require("electron");
const path = require("path");
const os = require("os");

// ===== FIX CACHE / GPU =====
const userDataPath = path.join(os.homedir(), "AppData", "Local", "MiPOS");
app.setPath("userData", userDataPath);
app.commandLine.appendSwitch(
  "disk-cache-dir",
  path.join(userDataPath, "Cache")
);
app.commandLine.appendSwitch(
  "gpu-shader-cache-dir",
  path.join(userDataPath, "GPUCache")
);
// ===========================

// Arranca PHP
require("./start-php");

function createWindow() {
  const win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      preload: path.join(__dirname, "preload.js"),
      nodeIntegration: false,
      contextIsolation: true,
    },
  });

  win.loadURL("http://127.0.0.1:8000/");
}

app.whenReady().then(createWindow);

app.on("window-all-closed", () => {
  if (process.platform !== "darwin") app.quit();
});
