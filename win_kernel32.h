typedef void * HINSTANCE;
typedef unsigned long DWORD;
typedef unsigned short WCHAR;

DWORD GetLastError();
HINSTANCE GetModuleHandleW(WCHAR *moduleName);
