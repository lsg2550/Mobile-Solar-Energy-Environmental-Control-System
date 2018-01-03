using Android.App;
using Android.Webkit;
using Android.OS;

namespace RemoteSite {
    [Activity(Label = "RemoteSite", MainLauncher = true)]
    public class MainActivity : Activity {
        //WebView
        private WebView webView;

        protected override void OnCreate(Bundle bundle) {
            SetContentView(Resource.Layout.Main);
            base.OnCreate(bundle);

            //Get WebView & Client
            webView = FindViewById<WebView>(Resource.Id.webView);
            webView.SetWebViewClient(new WebViewClient());
            webView.Settings.JavaScriptEnabled = true;

            //Load URL
            webView.LoadUrl("http://192.168.2.");
        }

        public override void OnBackPressed() {
            webView.GoBack();
        }
    }
}

