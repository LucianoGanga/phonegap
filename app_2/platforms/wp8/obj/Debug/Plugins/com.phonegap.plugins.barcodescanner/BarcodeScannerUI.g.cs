﻿#pragma checksum "E:\Dropbox\projects\www\phonegap\app_2\platforms\wp8\Plugins\com.phonegap.plugins.barcodescanner\BarcodeScannerUI.xaml" "{406ea660-64cf-4c82-b6f0-42d48172a799}" "307334E0395DA6121A3EC1BCBCF4EE00"
//------------------------------------------------------------------------------
// <auto-generated>
//     Este código fue generado por una herramienta.
//     Versión de runtime:4.0.30319.34014
//
//     Los cambios en este archivo podrían causar un comportamiento incorrecto y se perderán si
//     se vuelve a generar el código.
// </auto-generated>
//------------------------------------------------------------------------------

using Microsoft.Phone.Controls;
using System;
using System.Windows;
using System.Windows.Automation;
using System.Windows.Automation.Peers;
using System.Windows.Automation.Provider;
using System.Windows.Controls;
using System.Windows.Controls.Primitives;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Ink;
using System.Windows.Input;
using System.Windows.Interop;
using System.Windows.Markup;
using System.Windows.Media;
using System.Windows.Media.Animation;
using System.Windows.Media.Imaging;
using System.Windows.Resources;
using System.Windows.Shapes;
using System.Windows.Threading;


namespace WPCordovaClassLib.Cordova.Commands {
    
    
    public partial class BarcodeScannerUI : Microsoft.Phone.Controls.PhoneApplicationPage {
        
        internal System.Windows.Controls.Canvas CameraCanvas;
        
        internal System.Windows.Media.VideoBrush CameraBrush;
        
        private bool _contentLoaded;
        
        /// <summary>
        /// InitializeComponent
        /// </summary>
        [System.Diagnostics.DebuggerNonUserCodeAttribute()]
        public void InitializeComponent() {
            if (_contentLoaded) {
                return;
            }
            _contentLoaded = true;
            System.Windows.Application.LoadComponent(this, new System.Uri("/Aplicacion2;component/Plugins/com.phonegap.plugins.barcodescanner/BarcodeScanner" +
                        "UI.xaml", System.UriKind.Relative));
            this.CameraCanvas = ((System.Windows.Controls.Canvas)(this.FindName("CameraCanvas")));
            this.CameraBrush = ((System.Windows.Media.VideoBrush)(this.FindName("CameraBrush")));
        }
    }
}

