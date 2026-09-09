

typedef LRESULT (__stdcall *SUBCLASSPROC)( HWND, UINT, WPARAM, LPARAM,
   UINT_PTR sub, UINT_PTR ref );
typedef struct { SUBCLASSPROC f; } SSUBCLASSPROC;

BOOL SetWindowSubclass( HWND, SUBCLASSPROC, UINT_PTR sub, UINT_PTR ref );

LRESULT DefSubclassProc( HWND, UINT, WPARAM, LPARAM );
