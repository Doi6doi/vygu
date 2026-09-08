typedef LRESULT (__stdcall *WNDPROC)( HWND, UINT, WPARAM, LPARAM );
typedef struct { WNDPROC f; } SWNDPROC;
typedef struct { UINT size; UINT style; WNDPROC wndProc; int clsExtra;
   int wndExtra; HINSTANCE inst; HICON icon; HCURSOR cursor; 
   HBRUSH back; WSTR menuName; WSTR clsName; HICON iconSm; } WNDCLASSEXW;

HWND CreateWindowExW( DWORD exStyle, WSTR clsName,
   WSTR wndName, DWORD style, int x, int y, int width,
   int height, HWND parent, HMENU menu, HINSTANCE instance, 
   void * lParam );
LRESULT DefWindowProcW( HWND wnd, UINT Msg, WPARAM wParam, 
   LPARAM lParam );
BOOL IsWindowVisible( HWND wnd );
ATOM RegisterClassExW(WNDCLASSEXW *c);
BOOL SetWindowTextW( HWND wnd, WSTR s );

