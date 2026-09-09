
typedef int gint;
typedef unsigned int guint;
typedef uint32_t gunichar;
typedef int gboolean;
typedef void * gpointer;
typedef unsigned long gulong;
typedef unsigned long GType;

typedef struct _GdkMonitor GdkMonitor;
typedef struct _GdkDisplay GdkDisplay;
typedef struct _GListModel GListModel;
typedef struct _GMenu GMenu;
typedef struct _GtkApplication GtkApplication;
typedef struct _GtkEventController GtkEventController;
typedef struct _GtkLayoutManager GtkLayoutManager;
typedef struct _GtkTextBuffer GtkTextBuffer;
typedef struct _GtkWidget GtkWidget;

typedef gboolean (*SignalCallback)(gpointer, gpointer);
typedef int (*GtkCustomReqMode)( GtkWidget * );
typedef void (*GtkCustomMeasure)( GtkWidget *, int ori, int fors,
   int * min, int *nat, int * min_base, int * nat_base );
typedef void (*GtkCustomAllocate)( GtkWidget *,
   int width, int height, int baseline );
typedef gboolean (*KeyCallback)(gpointer cont, guint keyval,
   guint keycode, guint state, gpointer data);

typedef struct { int x; int y; int width; int height; } GdkRectangle;
typedef struct { GtkCustomMeasure measure; GtkCustomAllocate allocate; } sLayoutCallback;
typedef struct { KeyCallback c; } sKeyCallback;
typedef struct { SignalCallback c; } sSignalCallback;
typedef struct { gpointer dummy1; gpointer dummy2; int dummy3; int dummy4;
  int dummy5; int dummy6; int dummy7; int dummy8;
  gpointer dummy9; gpointer dummy10; int dummy11; int dummy12;
  int dummy13; gpointer dummy14;
} GtkTextIter;

typedef GdkRectangle GtkAllocation;

gpointer g_list_model_get_item( GListModel *list, guint position );
gboolean g_main_context_iteration( gpointer context, gboolean may_block );
GMenu * g_menu_new();
gpointer g_object_ref_sink(gpointer object);
void g_object_unref(gpointer object);
gulong g_signal_connect_data( gpointer instance, const char *detailed_signal,
   gpointer callback, gpointer data, gpointer destroy_data, int flags);
void g_signal_handler_disconnect( gpointer instance, gulong handler_id );
GdkDisplay *gdk_display_get_default(void);
GListModel *gdk_display_get_monitors(GdkDisplay *display);
gunichar gdk_keyval_to_unicode(guint keyval);
void gdk_monitor_get_geometry(GdkMonitor *monitor, GdkRectangle *geometry );
GtkWidget * gtk_button_new();
char *gtk_button_get_label(GtkWidget *button);
void gtk_button_set_label(GtkWidget *button, const char *label);
GtkLayoutManager * gtk_custom_layout_new( GtkCustomReqMode req_mode,
   GtkCustomMeasure measure, GtkCustomAllocate allocate );
GtkEventController * gtk_event_controller_key_new();
GtkWidget * gtk_fixed_new();
void gtk_init();
GtkWidget * gtk_label_new(const char *str);
const char * gtk_label_get_text(GtkWidget *self);
void gtk_label_set_text(GtkWidget *self,const char *str);
GtkWidget * gtk_scrolled_window_new();
void gtk_scrolled_window_set_child( GtkWidget *scrolled_window,
    GtkWidget *child );
void gtk_text_buffer_get_bounds( GtkTextBuffer *buffer, GtkTextIter *start,
    GtkTextIter *end );
void gtk_text_buffer_get_end_iter( GtkTextBuffer *buffer, GtkTextIter *iter );
void gtk_text_buffer_get_start_iter( GtkTextBuffer *buffer, GtkTextIter *iter );
char *gtk_text_buffer_get_text( GtkTextBuffer *buffer, const GtkTextIter *start,
    const GtkTextIter *end, gboolean include_hidden_chars );
void gtk_text_buffer_get_iter_at_offset( GtkTextBuffer *buffer, GtkTextIter *iter,
   gint char_offset );
void gtk_text_buffer_insert(GtkTextBuffer *buffer, GtkTextIter   *iter,
   const char    *text, int len );
void gtk_text_buffer_set_text(GtkTextBuffer *buffer, const char *text, int len );
GtkTextBuffer * gtk_text_view_get_buffer(GtkWidget *text_view);
GtkWidget * gtk_text_view_new();
void gtk_widget_add_controller( GtkWidget *widget, GtkEventController *cont);
int gtk_widget_get_allocation( GtkWidget *widget, GtkAllocation * alc );
int gtk_widget_get_height( GtkWidget *widget );
int gtk_widget_get_width( GtkWidget *widget );
gboolean gtk_widget_grab_focus( GtkWidget *widget);
void gtk_widget_measure(GtkWidget *widget, int ori, int fors, int *min, int *nat,
   int *min_base, int *nat_base);
void gtk_widget_set_cursor_from_name( GtkWidget *widget, const char *name);
void gtk_widget_set_layout_manager( GtkWidget *widget, GtkLayoutManager *lman);
void gtk_widget_set_parent(GtkWidget *widget, GtkWidget *parent);
void gtk_widget_set_visible(GtkWidget *widget, gboolean visible);
void gtk_widget_size_allocate(GtkWidget *widget, GtkAllocation * alc, int base );
void gtk_widget_unparent(GtkWidget *widget);
GtkWidget * gtk_window_new();
void gtk_window_set_child(GtkWidget *window, GtkWidget *child);
void gtk_window_set_title(GtkWidget *window, const char *title);
void gtk_window_set_default_size(GtkWidget *window, int width, int height);
void gtk_window_present(GtkWidget *window);


