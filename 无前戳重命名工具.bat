@echo off
setlocal enabledelayedexpansion

set count=1

:: 首先将所有文件夹重命名为临时名称
for /d %%f in (*) do (
    if not "%%f"=="lingshimingcheng_!count!" (
        ren "%%f" "lingshimingcheng_!count!"
    )
    set /a count+=1
)



set count=1

:: 遍历目录中的所有文件夹
for /d %%f in (*) do (
    ren "%%f" "!count!"
    set /a count=!count!+1
)

echo 重命名完成！
:: pause
