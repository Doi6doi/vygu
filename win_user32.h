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
int DrawTextW(HDC, WSTR text, int count, RECT *rect, UINT format);
BOOL GetClientRect(HWND hWnd, RECT *lpRect);
BOOL GetKeyboardState(BYTE *lpKeyState);
BOOL GetMessageW( MSG  *lpMsg, HWND hWnd, UINT wMsgFilterMin,
   UINT wMsgFilterMax );   
UINT_PTR GetWindowLongPtrW(HWND, int nIndex);
BOOL GetWindowRect(HWND, RECT *lpRect);
int GetWindowTextW( HWND, WCHAR * s, int nMaxCount );
int GetWindowTextLengthW( HWND );
BOOL InvalidateRect( HWND, const RECT *, BOOL erase );
BOOL IsWindowVisible( HWND );
HCURSOR LoadCursorW(HINSTANCE hInstance, WSTR lpCursorName);
BOOL MoveWindow(HWND, int X, int Y, int width, int height, BOOL repaint);
void PostQuitMessage(int nExitCode);
ATOM RegisterClassExW(WNDCLASSEXW *c);
HWND SetFocus(HWND);
HWND SetParent(HWND child, HWND hWndNewParent );
UINT_PTR SetWindowLongPtrW(HWND, int nIndex, LONG_PTR dwNewLong);   
BOOL SetWindowTextW( HWND, WSTR s );
BOOL ShowWindow(HWND, int nCmdShow);
BOOL SystemParametersInfoW( UINT uiAction, UINT uiParam, void *pvParam,
   UINT fWinIni );
int ToUnicode( UINT vk, UINT scan, const BYTE *state, WSTR buf,
   int lbuf, UINT flags );
BOOL TranslateMessage(const MSG *lpMsg);
BOOL UnregisterClassW(const WCHAR *lpClassName, HINSTANCE hInstance);
