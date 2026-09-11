
typedef int gint;
typedef unsigned int guint;
typedef uint32_t gunichar;
typedef int gboolean;
typedef void * gpointer;
typedef unsigned long gulong;
typedef unsigned long GType;
typedef const char * Str;
typedef struct _Elem * Elem;

typedef struct _GdkMonitor GdkMonitor;
typedef struct _GdkDisplay GdkDisplay;
typedef struct _GFile GFile;
typedef struct _GListModel GListModel;
typedef struct _GMenu GMenu;
typedef struct _GtkApplication GtkApplication;
typedef struct _GTkCssProvider GtkCssProvider;
typedef struct _GtkEventController GtkEventController;
typedef struct _GtkLayoutManager GtkLayoutManager;
typedef struct _GtkTextBuffer GtkTextBuffer;
typedef struct _GtkShortcut GtkShortcut;

typedef gboolean (*SignalCallback)(gpointer, gpointer);
typedef void (*GAsyncReadyCallback)( gpointer source,
   gpointer res, gpointer user_data );
typedef int (*GtkCustomReqMode)( Elem );
typedef void (*GtkCustomMeasure)( Elem, int ori, int fors,
   int * min, int *nat, int * min_base, int * nat_base );
typedef void (*GtkCustomAllocate)( Elem,
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

void g_action_map_add_action( gpointer map, Elem action );
char *g_file_get_path( GFile *file );
void g_free( gpointer );
gpointer g_list_model_get_item( GListModel *list, guint position );
gboolean g_main_context_iteration( gpointer context, gboolean may_block );
void g_menu_append( GMenu *menu, Str label, Str detailed_action );
void g_menu_append_submenu( GMenu *menu, Str label, GMenu *submenu );
GMenu * g_menu_new();
gpointer g_object_ref_sink(gpointer object);
void g_object_unref(gpointer object);
gulong g_signal_connect_data( gpointer instance, Str detailed_signal,
   gpointer callback, gpointer data, gpointer destroy_data, int flags);
void g_signal_handler_disconnect( gpointer instance, gulong handler_id );
gpointer g_simple_action_group_new();
Elem g_simple_action_new( Str name, gpointer parameter_type );
GdkDisplay *gdk_display_get_default(void);
GListModel *gdk_display_get_monitors(GdkDisplay *display);
gunichar gdk_keyval_to_unicode(guint keyval);
void gdk_monitor_get_geometry(GdkMonitor *monitor, GdkRectangle *geometry );
void gtk_box_prepend( Elem box, Elem child );
void gtk_box_append( Elem box, Elem child );
Elem gtk_box_new( int ori, int spacing );
Elem gtk_button_new();
Str gtk_button_get_label(Elem button);
void gtk_button_set_label(Elem button, Str label);
GtkCssProvider * gtk_css_provider_new();
void gtk_css_provider_load_from_string( GtkCssProvider *, Str );
GtkLayoutManager * gtk_custom_layout_new( GtkCustomReqMode req_mode,
   GtkCustomMeasure measure, GtkCustomAllocate allocate );
GtkEventController * gtk_event_controller_key_new();
void gtk_file_dialog_open( gpointer self, Elem parent, gpointer cancel,
   GAsyncReadyCallback callback, gpointer user_data );
GFile *gtk_file_dialog_open_finish( gpointer self, gpointer result, 
   gpointer error );
gpointer gtk_file_dialog_new();
Elem gtk_fixed_new();
void gtk_init();
Elem gtk_label_new(Str);
Str gtk_label_get_text(Elem);
float gtk_label_get_xalign(Elem);
void gtk_label_set_text(Elem, Str);
void gtk_label_set_xalign(Elem, float);
Elem gtk_popover_menu_bar_new_from_model( GMenu * );
Elem gtk_scrolled_window_new();
void gtk_scrolled_window_set_child( Elem scrolled_window, Elem child );
gpointer gtk_shortcut_action_parse_string(Str string);
GtkShortcut * gtk_shortcut_new( gpointer trigger, gpointer action );
gpointer gtk_shortcut_trigger_parse_string(Str string);    
void gtk_style_context_add_provider_for_display( gpointer display,
    gpointer provider, guint priority );
void gtk_text_buffer_get_bounds( GtkTextBuffer *buffer, GtkTextIter *start,
    GtkTextIter *end );
void gtk_text_buffer_get_end_iter( GtkTextBuffer *buffer, GtkTextIter *iter );
void gtk_text_buffer_get_start_iter( GtkTextBuffer *buffer, GtkTextIter *iter );
Str gtk_text_buffer_get_text( GtkTextBuffer *buffer, const GtkTextIter *start,
    const GtkTextIter *end, gboolean include_hidden_chars );
void gtk_text_buffer_get_iter_at_offset( GtkTextBuffer *buffer, GtkTextIter *iter,
   gint char_offset );
void gtk_text_buffer_insert(GtkTextBuffer *buffer, GtkTextIter *iter,
   Str text, int len );
void gtk_text_buffer_set_text(GtkTextBuffer *buffer, Str text, int len );
GtkTextBuffer * gtk_text_view_get_buffer(Elem text_view);
Elem gtk_text_view_new();
void gtk_widget_add_controller( Elem widget, GtkEventController *cont);
int gtk_widget_get_allocation( Elem widget, GtkAllocation * alc );
int gtk_widget_get_height( Elem widget );
int gtk_widget_get_width( Elem widget );
gboolean gtk_widget_grab_focus( Elem widget);
void gtk_widget_insert_action_group( Elem, Str name, gpointer group );
void gtk_widget_measure(Elem widget, int ori, int fors, int *min, int *nat,
   int *min_base, int *nat_base);
void gtk_widget_set_cursor_from_name( Elem, Str );
void gtk_widget_set_hexpand( Elem widget, gboolean expand );
void gtk_widget_set_layout_manager( Elem, GtkLayoutManager *lman);
void gtk_widget_set_parent(Elem widget, Elem parent);
void gtk_widget_set_vexpand( Elem, gboolean);
void gtk_widget_set_visible(Elem, gboolean);
void gtk_widget_size_allocate(Elem, GtkAllocation * alc, int base );
void gtk_widget_unparent(Elem);
Elem gtk_window_new();
void gtk_window_set_child(Elem window, Elem child);
void gtk_window_set_title(Elem, Str);
void gtk_window_set_default_size(Elem, int width, int height);
void gtk_window_present(Elem);


