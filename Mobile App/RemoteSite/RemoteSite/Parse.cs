using System.Text.RegularExpressions;

namespace RemoteSite {
    public class Parse {
        private static readonly Regex whitespace = new Regex(@"\s+");
        public static string ReplaceWhiteSpace(string input) => whitespace.Replace(input, string.Empty);
    }
}