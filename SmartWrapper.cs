using System;
using System.IO;
using System.Diagnostics;

namespace FoodFlowWrapper {
    static class Program {
        [STAThread]
        static void Main(string[] args) {
            try {
                string currentExe = Process.GetCurrentProcess().MainModule.FileName;
                string appData = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
                string targetDir = Path.Combine(appData, "FoodFlow");
                string targetExe = Path.Combine(targetDir, "FoodFlow.exe");

                if (!currentExe.Equals(targetExe, StringComparison.OrdinalIgnoreCase)) {
                    // Running as installer
                    if (!Directory.Exists(targetDir)) {
                        Directory.CreateDirectory(targetDir);
                    }
                    File.Copy(currentExe, targetExe, true);
                    
                    // Create Shortcut
                    string desktop = Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory);
                    string linkPath = Path.Combine(desktop, "FoodFlow.lnk");
                    string vbsPath = Path.Combine(targetDir, "shortcut.vbs");
                    File.WriteAllText(vbsPath, "Set ws = CreateObject(\"WScript.Shell\")\nSet link = ws.CreateShortcut(\"" + linkPath + "\")\nlink.TargetPath = \"" + targetExe + "\"\nlink.WorkingDirectory = \"" + targetDir + "\"\nlink.Save()");
                    Process.Start(new ProcessStartInfo { FileName = "cscript.exe", Arguments = "//Nologo \"" + vbsPath + "\"", WindowStyle = ProcessWindowStyle.Hidden }).WaitForExit();
                    File.Delete(vbsPath);
                    
                    // Launch installed app
                    Process.Start(new ProcessStartInfo { FileName = targetExe, UseShellExecute = true });
                    return; // Exit installer
                }

                // If running from AppData (installed), launch the web wrapper
                string url = "http://foodfloow.uz";
                try {
                    ProcessStartInfo startInfo = new ProcessStartInfo();
                    startInfo.FileName = "msedge";
                    startInfo.Arguments = "--app=" + url;
                    startInfo.UseShellExecute = true;
                    startInfo.WindowStyle = ProcessWindowStyle.Normal;
                    Process.Start(startInfo);
                } catch {
                    try {
                        ProcessStartInfo startInfo2 = new ProcessStartInfo();
                        startInfo2.FileName = "chrome";
                        startInfo2.Arguments = "--app=" + url;
                        startInfo2.UseShellExecute = true;
                        startInfo2.WindowStyle = ProcessWindowStyle.Normal;
                        Process.Start(startInfo2);
                    } catch {
                        ProcessStartInfo startInfo3 = new ProcessStartInfo();
                        startInfo3.FileName = url;
                        startInfo3.UseShellExecute = true;
                        Process.Start(startInfo3);
                    }
                }
            } catch {
                // Ignore errors
            }
        }
    }
}
