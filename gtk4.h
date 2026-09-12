
typedef int gint;
typedef unsigned int guint;
typedef uint32_t gunichar;
typedef unsigned long gulong;
typedef unsigned long GType;
typedef int Bool;
typedef void * Ptr;
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

typedef Bool (*SignalCallback)(Ptr, Ptr);
typedef void (*GAsyncReadyCallback)( Ptr source,
   Ptr res, Ptr user_data );
typedef int (*GtkCustomReqMode)( Elem );
typedef void (*GtkCustomMeasure)( Elem, int ori, int fors,
   int * min, int *nat, int * min_base, int * nat_base );
typedef void (*GtkCustomAllocate)( Elem,
   int width, int height, int baseline );
typedef Bool (*KeyCallback)(Ptr cont, guint keyval,
   guint keycode, guint state, Ptr data);

typedef struct { int x; int y; int width; int height; } GdkRectangle;
typedef struct { GtkCustomMeasure measure; GtkCustomAllocate allocate; } sLayoutCallback;
typedef struct { KeyCallback c; } sKeyCallback;
typedef struct { SignalCallback c; } sSignalCallback;
typedef struct { Ptr dummy1; Ptr dummy2; int dummy3; int dummy4;
  int dummy5; int dummy6; int dummy7; int dummy8;
  Ptr dummy9; Ptr dummy10; int dummy11; int dummy12;
  int dummy13; Ptr dummy14;
} GtkTextIter;

typedef GdkRectangle GtkAllocation;

void g_action_map_add_action( Ptr map, Elem action );
char *g_file_get_path( GFile *file );
void g_free( Ptr );
Ptr g_list_model_get_item( GListModel *list, guint position );
Bool g_main_context_iteration( Ptr context, Bool may_block );
void g_menu_append( GMenu *menu, Str label, Str detailed_action );
void g_menu_append_submenu( GMenu *menu, Str label, GMenu *submenu );
GMenu * g_menu_new();
Ptr g_object_ref_sink(Ptr object);
void g_object_unref(Ptr object);
gulong g_signal_connect_data( Ptr instance, Str detailed_signal,
   Ptr callback, Ptr data, Ptr destroy_data, int flags);
void g_signal_handler_disconnect( Ptr instance, gulong handler_id );
Ptr g_simple_action_group_new();
Elem g_simple_action_new( Str name, Ptr parameter_type );
void gdk_clipboard_read_text_async( Ptr clipboard, Ptr cancel, 
   GAsyncReadyCallback callback, Ptr user_data );
Str gdk_clipboard_read_text_finish( Ptr clipboard, 
   Ptr result, Ptr error );
void gdk_clipboard_set_text( Ptr, Str );
Ptr gdk_display_get_clipboard( GdkDisplay * );
GdkDisplay *gdk_display_get_default(void);
GListModel *gdk_display_get_monitors(GdkDisplay *display);
gunichar gdk_keyval_to_unicode(guint keyval);
void gdk_monitor_get_geometry(GdkMonitor *monitor, GdkRectangle *geometry );
void gtk_alert_dialog_choose( Ptr self, Ptr parent, Ptr cancel,
   GAsyncReadyCallback callback, Ptr user_data );
int gtk_alert_dialog_choose_finish( Ptr self, Ptr result, Ptr error );
Ptr gtk_alert_dialog_new( Str format, ... );
void gtk_alert_dialog_set_buttons( Ptr self, char ** labels );
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
void gtk_file_dialog_open( Ptr self, Elem parent, Ptr cancel,
   GAsyncReadyCallback callback, Ptr user_data );
GFile *gtk_file_dialog_open_finish( Ptr self, Ptr result, 
   Ptr error );
void gtk_file_dialog_save( Ptr self, Elem parent, Ptr cancel,
   GAsyncReadyCallback callback, Ptr user_data );
GFile *gtk_file_dialog_save_finish( Ptr self, Ptr result, 
   Ptr error );
Ptr gtk_file_dialog_new();
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
Ptr gtk_shortcut_action_parse_string(Str string);
GtkShortcut * gtk_shortcut_new( Ptr trigger, Ptr action );
Ptr gtk_shortcut_trigger_parse_string(Str string);    
void gtk_style_context_add_provider_for_display( Ptr display,
    Ptr provider, guint priority );
void gtk_text_buffer_delete( GtkTextBuffer *,
    GtkTextIter *start, GtkTextIter *end );    
int gtk_text_buffer_get_char_count(GtkTextBuffer *);    
Str gtk_text_buffer_get_text( GtkTextBuffer *, const GtkTextIter *start,
    const GtkTextIter *end, Bool include_hidden_chars );
void gtk_text_buffer_get_iter_at_offset( GtkTextBuffer *, GtkTextIter *,
   gint char_offset );
Bool gtk_text_buffer_get_selection_bounds( GtkTextBuffer *buffer,
    GtkTextIter *start, GtkTextIter *end );   
void gtk_text_buffer_insert(GtkTextBuffer *buffer, GtkTextIter *iter,
   Str text, int len );
void gtk_text_buffer_set_text(GtkTextBuffer *buffer, Str text, int len );
int gtk_text_iter_get_offset(GtkTextIter *iter);
GtkTextBuffer * gtk_text_view_get_buffer(Elem text_view);
Elem gtk_text_view_new();
void gtk_widget_add_controller( Elem widget, GtkEventController *cont);
int gtk_widget_get_allocation( Elem widget, GtkAllocation * alc );
int gtk_widget_get_height( Elem widget );
int gtk_widget_get_width( Elem widget );
Bool gtk_widget_grab_focus( Elem widget);
void gtk_widget_insert_action_group( Elem, Str name, Ptr group );
void gtk_widget_measure(Elem widget, int ori, int fors, int *min, int *nat,
   int *min_base, int *nat_base);
void gtk_widget_set_cursor_from_name( Elem, Str );
void gtk_widget_set_hexpand( Elem widget, Bool expand );
void gtk_widget_set_layout_manager( Elem, GtkLayoutManager *lman);
void gtk_widget_set_parent(Elem widget, Elem parent);
void gtk_widget_set_vexpand( Elem, Bool);
void gtk_widget_set_visible(Elem, Bool);
void gtk_widget_size_allocate(Elem, GtkAllocation * alc, int base );
void gtk_widget_unparent(Elem);
Elem gtk_window_new();
void gtk_window_set_child(Elem window, Elem child);
void gtk_window_set_title(Elem, Str);
void gtk_window_set_default_size(Elem, int width, int height);
void gtk_window_present(Elem);


