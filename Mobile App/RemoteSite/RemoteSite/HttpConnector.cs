using System.Net.Http;

namespace RemoteSite {
    public class HttpConnector {
        //HttpClient - Communication to the Web
        public static readonly HttpClient client = new HttpClient();

        //ResponseCode Enum
        public enum ResponseCodes { OK, NO }
    }
}