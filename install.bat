@echo off
mkdir "%APPDATA%\FoodFlow" 2>nul
copy /y foodflow-windows.exe "%APPDATA%\FoodFlow\foodflow.exe"
echo Set oWS = WScript.CreateObject("WScript.Shell") > create_shortcut.vbs
echo sLinkFile = "%USERPROFILE%\Desktop\FoodFlow.lnk" >> create_shortcut.vbs
echo Set oLink = oWS.CreateShortcut(sLinkFile) >> create_shortcut.vbs
echo oLink.TargetPath = "%APPDATA%\FoodFlow\foodflow.exe" >> create_shortcut.vbs
echo oLink.Save >> create_shortcut.vbs
cscript create_shortcut.vbs
del create_shortcut.vbs
