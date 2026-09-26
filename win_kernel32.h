

typedef struct { ULONG cbSize; DWORD dwFlags; WSTR lpSource;
   WORD wProcessorArchitecture; WORD wLangId; 
   WSTR lpAssemblyDirectory; WSTR lpResourceName; 
   WSTR lpApplicationName; HMODULE hModule;
} ACTCTXW;

BOOL ActivateActCtx(HANDLE, ULONG_PTR *lpCookie);
HANDLE CreateActCtxW(const ACTCTXW *);
DWORD GetLastError();
HINSTANCE GetModuleHandleW(WCHAR *moduleName);
HANDLE GlobalAlloc(UINT uFlags, SIZE_T dwBytes);
void* GlobalLock(HANDLE);
BOOL    GlobalUnlock(HANDLE);
DWORD FormatMessageW( DWORD dwFlags, void * lpSource,
   DWORD dwMessageId, DWORD dwLanguageId, WSTR lpBuffer,
   DWORD nSize, void * Arguments );
int lstrlenW(WSTR lpString);
