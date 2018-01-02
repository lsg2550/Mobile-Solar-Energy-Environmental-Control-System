using Android.App;
using Android.Webkit;
using Android.OS;
using RemoteSite.Views;
using RemoteSite.Models;

namespace RemoteSite {
    [Activity(Label = "RemoteSite", MainLauncher = true)]
    public class MainActivity : Activity {
        //WebVie
        private WebView webView;

        protected override void OnCreate(Bundle bundle) {
            SetContentView(Resource.Layout.Main);
            base.OnCreate(bundle);

            //Get WebView & Client
            webView = FindViewById<WebView>(Resource.Id.webView);
            webView.SetWebViewClient(new WebViewClient());
            webView.Settings.JavaScriptEnabled = true;

            //Load URL
            webView.LoadUrl("file:///android_asset/index.html");
            
        }

        public override void OnBackPressed() {
            webView.GoBack();
        }
    }
}

