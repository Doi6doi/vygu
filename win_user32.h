typedef void * HWND;
typedef void * HINSTANCE;
typedef void * HMENU;
typedef void * HICON;
typedef void * HCURSOR;
typedef void * HBRUSH;
typedef int BOOL;
typedef unsigned int UINT;
typedef unsigned short WCHAR;
typedef unsigned short WORD;
typedef WORD ATOM;
typedef unsigned long DWORD;
typedef unsigned long long WPARAM;
typedef long long LPARAM;
typedef long long LRESULT;
typedef const WCHAR * WSTR;

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
ATOM RegisterClassExW(WNDCLASSEXW *c);
BOOL SetWindowTextW( HWND wnd, WSTR s );

