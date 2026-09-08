typedef LRESULT (__stdcall *WNDPROC)( HWND, UINT, WPARAM, LPARAM );
typedef struct { WNDPROC f; } SWNDPROC;
typedef struct { UINT size; UINT style; WNDPROC wndProc; int clsExtra;
   int wndExtra; HINSTANCE inst; HICON icon; HCURSOR cursor; 
   HBRUSH back; WSTR menuName; WSTR clsName; HICON iconSm; } WNDCLASSEXW;
typedef struct { LONG x; LONG y; } POINT;
typedef struct { HWND hwnd; UINT message; WPARAM wParam; LPARAM lParam;
    DWORD time; POINT pt; DWORD lPrivate; } MSG;

HWND CreateWindowExW( DWORD exStyle, WSTR clsName,
   WSTR wndName, DWORD style, int x, int y, int width,
   int height, HWND parent, HMENU menu, HINSTANCE instance, 
   void * lParam );
LRESULT DefWindowProcW( HWND wnd, UINT Msg, WPARAM wParam, 
   LPARAM lParam );
LRESULT DispatchMessageW(const MSG *lpMsg);   
BOOL GetClientRect(HWND hWnd, RECT *lpRect);
BOOL GetMessageW( MSG  *lpMsg, HWND hWnd, UINT wMsgFilterMin,
   UINT wMsgFilterMax );   
LONG_PTR GetWindowLongPtrW(HWND hWnd, int nIndex);
BOOL GetWindowRect(HWND hWnd, RECT *lpRect);
BOOL IsWindowVisible( HWND wnd );
HCURSOR LoadCursorW(HINSTANCE hInstance, WSTR lpCursorName);
BOOL MoveWindow(HWND, int X, int Y, int width, int height, BOOL repaint);
void PostQuitMessage(int nExitCode);
ATOM RegisterClassExW(WNDCLASSEXW *c);
HWND SetParent( HWND hWndChild, HWND hWndNewParent );
LONG_PTR SetWindowLongPtrW(HWND hWnd, int nIndex, LONG_PTR dwNewLong);   
BOOL SetWindowTextW( HWND wnd, WSTR s );
BOOL ShowWindow(HWND hWnd, int nCmdShow);
BOOL SystemParametersInfoW( UINT uiAction, UINT uiParam, void *pvParam,
   UINT fWinIni );
BOOL TranslateMessage(const MSG *lpMsg);
BOOL UnregisterClassW(const WCHAR *lpClassName, HINSTANCE hInstance);
