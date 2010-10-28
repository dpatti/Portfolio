#include "my.h"
#include <gtk/gtk.h>

void destroyEvent();
gint deleteEvent();
void daytimeClick();
void nighttimeClick();
void confirmYes();

int main(int argc, char **argv){
  GtkWidget *window, *button, *box, *vbox, *bbox, *img, *label, *confirm;
  gtk_init(&argc, &argv);

  //creating confirm dialog now so that we can reference it on exit clicked

  confirm = gtk_window_new(GTK_WINDOW_TOPLEVEL);
  // ------------------------------------------------------ 
  // Main Window
  // ------------------------------------------------------
  window = gtk_window_new(GTK_WINDOW_TOPLEVEL);
  gtk_window_set_title(GTK_WINDOW(window), "Test");
  g_signal_connect(G_OBJECT(window), "destroy", G_CALLBACK(destroyEvent), NULL);
  g_signal_connect(G_OBJECT(window), "delete_event", G_CALLBACK(deleteEvent), NULL);
  gtk_container_set_border_width(GTK_CONTAINER(window), 10);
  
  vbox = gtk_vbox_new(FALSE, 0);
  gtk_container_add(GTK_CONTAINER(window), vbox);

  box = gtk_hbox_new(FALSE, 0);
  gtk_box_pack_start(GTK_BOX(vbox), box, TRUE, TRUE, 0);
  
  //Daytime button
  button = gtk_button_new();
  g_signal_connect(G_OBJECT(button), "clicked", G_CALLBACK(daytimeClick), NULL);
  bbox = gtk_hbox_new(FALSE, 0);
  img = gtk_image_new_from_file("sun.png");
  gtk_box_pack_start(GTK_BOX(bbox), img, TRUE, TRUE, 0);
  label = gtk_label_new("Daytime");
  gtk_box_pack_start(GTK_BOX(bbox), label, TRUE, TRUE, 0);
  gtk_container_add(GTK_CONTAINER(button), bbox);
  gtk_box_pack_start(GTK_BOX(box), button, TRUE, TRUE, 0);
  gtk_widget_show(img);
  gtk_widget_show(label);
  gtk_widget_show(bbox);
  gtk_widget_show(button);

  //Nighttime button
  button = gtk_button_new();
  g_signal_connect(G_OBJECT(button), "clicked", G_CALLBACK(nighttimeClick), NULL);
  bbox = gtk_hbox_new(FALSE, 0);
  img = gtk_image_new_from_file("moon.png");
  gtk_box_pack_start(GTK_BOX(bbox), img, TRUE, TRUE, 0);
  label = gtk_label_new("Nighttime");
  gtk_box_pack_start(GTK_BOX(bbox), label, TRUE, TRUE, 0);
  gtk_container_add(GTK_CONTAINER(button), bbox);
  gtk_box_pack_start(GTK_BOX(box), button, TRUE, TRUE, 0);
  gtk_widget_show(img);
  gtk_widget_show(label);
  gtk_widget_show(bbox);
  gtk_widget_show(button);

  //Exit button
  button = gtk_button_new();
  g_signal_connect_swapped(G_OBJECT(button), "clicked", G_CALLBACK(gtk_widget_show), GTK_OBJECT(confirm));
  bbox = gtk_hbox_new(FALSE, 0);
  img = gtk_image_new_from_file("exit.png");
  gtk_box_pack_start(GTK_BOX(bbox), img, TRUE, TRUE, 0);
  label = gtk_label_new("Exit");
  gtk_box_pack_start(GTK_BOX(bbox), label, TRUE, TRUE, 0);
  gtk_container_add(GTK_CONTAINER(button), bbox);
  gtk_box_pack_start(GTK_BOX(vbox), button, TRUE, TRUE, 0);
  gtk_widget_show(img);
  gtk_widget_show(label);
  gtk_widget_show(bbox);
  gtk_widget_show(button);

  //Show Main Widgets
  gtk_widget_show(box);
  gtk_widget_show(vbox);
  gtk_widget_show(window);

  // ------------------------------------------------------ 
  // Exit Prompt
  // ------------------------------------------------------
  gtk_window_set_title(GTK_WINDOW(confirm), "Confirmation");
  g_signal_connect(G_OBJECT(confirm), "destroy", G_CALLBACK(gtk_widget_hide), NULL);
  g_signal_connect(G_OBJECT(confirm), "delete_event", G_CALLBACK(gtk_widget_hide), NULL);
  gtk_container_set_border_width(GTK_CONTAINER(confirm), 10);

  vbox = gtk_vbox_new(FALSE, 0);
  gtk_container_add(GTK_CONTAINER(confirm), vbox);
  
  //Message
  label = gtk_label_new("Are you sure you want to exit?");
  gtk_box_pack_start(GTK_BOX(vbox), label, TRUE, TRUE, 0);
  gtk_widget_show(label);

  box = gtk_hbox_new(FALSE, 0);
  gtk_box_pack_start(GTK_BOX(vbox), box, TRUE, TRUE, 0);

  //Yes
  button = gtk_button_new();
  g_signal_connect(G_OBJECT(button), "clicked", G_CALLBACK(confirmYes), NULL);
  bbox = gtk_hbox_new(FALSE, 0);
  img = gtk_image_new_from_file("yes.png");
  gtk_box_pack_start(GTK_BOX(bbox), img, TRUE, TRUE, 0);
  label = gtk_label_new("Yes");
  gtk_box_pack_start(GTK_BOX(bbox), label, TRUE, TRUE, 0);
  gtk_container_add(GTK_CONTAINER(button), bbox);
  gtk_box_pack_start(GTK_BOX(box), button, TRUE, TRUE, 0);
  gtk_widget_show(img);
  gtk_widget_show(label);
  gtk_widget_show(bbox);
  gtk_widget_show(button);

  //No
  button = gtk_button_new();
  g_signal_connect_swapped(G_OBJECT(button), "clicked", G_CALLBACK(gtk_widget_hide), G_OBJECT(confirm));
  bbox = gtk_hbox_new(FALSE, 0);
  img = gtk_image_new_from_file("no.png");
  gtk_box_pack_start(GTK_BOX(bbox), img, TRUE, TRUE, 0);
  label = gtk_label_new("No");
  gtk_box_pack_start(GTK_BOX(bbox), label, TRUE, TRUE, 0);
  gtk_container_add(GTK_CONTAINER(button), bbox);
  gtk_box_pack_start(GTK_BOX(box), button, TRUE, TRUE, 0);
  gtk_widget_show(img);
  gtk_widget_show(label);
  gtk_widget_show(bbox);
  gtk_widget_show(button);
  
  gtk_widget_show(box);
  gtk_widget_show(vbox);


  //Begin
  gtk_main();
  return 0;
}

void destroyEvent(){
  gtk_main_quit();
}

gint deleteEvent(){
  my_str("Use exit button plox\n");
  return TRUE;
}

void daytimeClick(){
  my_str("The sun is rising; you should probably go to sleep now.\n");
}

void nighttimeClick(){
  my_str("All that bad bright light is gone; it's safe to wake up.\n");
}

void confirmYes(){
  gtk_main_quit();
}
