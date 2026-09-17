typedef struct {
    DWORD        lStructSize;
    HWND         hwndOwner;
    HINSTANCE    hInstance;
    const WCHAR *lpstrFilter;
    WCHAR       *lpstrCustomFilter;
    DWORD        nMaxCustFilter;
    DWORD        nFilterIndex;
    WCHAR       *lpstrFile;
    DWORD        nMaxFile;
    WCHAR       *lpstrFileTitle;
    DWORD        nMaxFileTitle;
    const WCHAR *lpstrInitialDir;
    const WCHAR *lpstrTitle;
    DWORD        Flags;
    WORD         nFileOffset;
    WORD         nFileExtension;
    const WCHAR *lpstrDefExt;
    LPARAM       lCustData;
    void        *lpfnHook;
    const WCHAR *lpTemplateName;
    void        *pvReserved;
    DWORD        dwReserved;
    DWORD        FlagsEx;
} OPENFILENAMEW;

BOOL GetOpenFileNameW(OPENFILENAMEW *ofn);
BOOL GetSaveFileNameW(OPENFILENAMEW *ofn);
