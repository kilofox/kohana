<style>
    #kohana_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
    #kohana_error h1,
    #kohana_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
    #kohana_error h1 a,
    #kohana_error h2 a { color: #fff; }
    #kohana_error h2 { background: #222; }
    #kohana_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
    #kohana_error p { margin: 0; padding: 0.2em 0; }
    #kohana_error a { color: #1b323b; }
    #kohana_error pre { overflow: auto; white-space: pre-wrap; }
    #kohana_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
    #kohana_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
    #kohana_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
    #kohana_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
    #kohana_error pre.source span.line { display: block; }
    #kohana_error pre.source span.highlight { background: #f0eb96; }
    #kohana_error pre.source span.line span.number { color: #666; }
    #kohana_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
    #kohana_error ol.trace li { margin: 0; padding: 0; }
    .js .collapsed { display: none; }
</style>
<script type="text/javascript">
    document.documentElement.className = document.documentElement.className + ' js';
    function koggle(elem)
    {
        // Only works with the "style" attr
        let disp;
        elem = document.getElementById(elem);

        if (elem.style && elem.style['display']) {
            disp = elem.style['display'];
        } else if (window.getComputedStyle) {
            disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
        }

        // Toggle the state of the "display" style
        elem.style.display = disp === 'block' ? 'none' : 'block';
        return false;
    }
</script>
<div id="kohana_error">
    <h1><span class="type">View_Exception [ 0 ]:</span> <span class="message">The requested view site could not be found</span></h1>
    <div id="error68b8f79f31e72" class="content">
        <p><span class="file">SYSPATH/classes/Kohana/View.php [ 247 ]</span></p>
        <pre class="source">
            <code><span class="line"><span class="number">242</span>      * @throws  View_Exception
</span><span class="line"><span class="number">243</span>      */
</span><span class="line"><span class="number">244</span>     public function set_filename(string $file): Kohana_View
</span><span class="line"><span class="number">245</span>     {
</span><span class="line"><span class="number">246</span>         if (!$path = Kohana::find_file('views', $file)) {
</span><span class="line highlight"><span class="number">247</span>             throw new View_Exception('The requested view :file could not be found', [':file' =&gt; $file]);
</span><span class="line"><span class="number">248</span>         }
</span><span class="line"><span class="number">249</span>
</span><span class="line"><span class="number">250</span>         // Store the file path locally
</span><span class="line"><span class="number">251</span>         $this-&gt;_file = $path;
</span><span class="line"><span class="number">252</span>
</span></code>
        </pre>
        <ol class="trace">
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source0" onclick="return koggle('error68b8f79f31e72source0')">SYSPATH/classes/Kohana/View.php [ 138 ]</a>
                    </span>
                    &raquo;
                    Kohana_View->set_filename(<a href="#error68b8f79f31e72args0" onclick="return koggle('error68b8f79f31e72args0')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args0" class="collapsed">
                    <table>
                        <tr>
                            <td><code>file</code></td>
                            <td><pre><small>string</small><span>(4)</span> "site"</pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source0" class="source collapsed"><code><span class="line"><span class="number">133</span>      * @uses    View::set_filename
</span><span class="line"><span class="number">134</span>      */
</span><span class="line"><span class="number">135</span>     public function __construct(string $file = null, array $data = null)
</span><span class="line"><span class="number">136</span>     {
</span><span class="line"><span class="number">137</span>         if ($file !== null) {
</span><span class="line highlight"><span class="number">138</span>             $this-&gt;set_filename($file);
</span><span class="line"><span class="number">139</span>         }
</span><span class="line"><span class="number">140</span>
</span><span class="line"><span class="number">141</span>         if ($data !== null) {
</span><span class="line"><span class="number">142</span>             // Add the values to the current data
</span><span class="line"><span class="number">143</span>             $this-&gt;_data = $data + $this-&gt;_data;
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source1" onclick="return koggle('error68b8f79f31e72source1')">SYSPATH/classes/Kohana/View.php [ 32 ]</a>
                    </span>
                    &raquo;
                    Kohana_View->__construct(<a href="#error68b8f79f31e72args1" onclick="return koggle('error68b8f79f31e72args1')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args1" class="collapsed">
                    <table>
                        <tr>
                            <td><code>file</code></td>
                            <td><pre><small>string</small><span>(4)</span> "site"</pre></td>
                        </tr>
                        <tr>
                            <td><code>data</code></td>
                            <td><pre><small>NULL</small></pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source1" class="source collapsed"><code><span class="line"><span class="number">27</span>      * @return  View
</span><span class="line"><span class="number">28</span>      * @throws View_Exception
</span><span class="line"><span class="number">29</span>      */
</span><span class="line"><span class="number">30</span>     public static function factory(string $file = null, array $data = null): View
</span><span class="line"><span class="number">31</span>     {
</span><span class="line highlight"><span class="number">32</span>         return new View($file, $data);
</span><span class="line"><span class="number">33</span>     }
</span><span class="line"><span class="number">34</span>
</span><span class="line"><span class="number">35</span>     /**
</span><span class="line"><span class="number">36</span>      * Captures the output that is generated when a view is included.
</span><span class="line"><span class="number">37</span>      * The view data will be extracted to make local variables. This method
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source2" onclick="return koggle('error68b8f79f31e72source2')">SYSPATH/classes/Kohana/Controller/Template.php [ 33 ]</a>
                    </span>
                    &raquo;
                    Kohana_View::factory(<a href="#error68b8f79f31e72args2" onclick="return koggle('error68b8f79f31e72args2')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args2" class="collapsed">
                    <table>
                        <tr>
                            <td><code>file</code></td>
                            <td><pre><small>string</small><span>(4)</span> "site"</pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source2" class="source collapsed"><code><span class="line"><span class="number">28</span>     {
</span><span class="line"><span class="number">29</span>         parent::before();
</span><span class="line"><span class="number">30</span>
</span><span class="line"><span class="number">31</span>         if ($this-&gt;auto_render === true) {
</span><span class="line"><span class="number">32</span>             // Load the template
</span><span class="line highlight"><span class="number">33</span>             $this-&gt;template = View::factory($this-&gt;template);
</span><span class="line"><span class="number">34</span>         }
</span><span class="line"><span class="number">35</span>     }
</span><span class="line"><span class="number">36</span>
</span><span class="line"><span class="number">37</span>     /**
</span><span class="line"><span class="number">38</span>      * Assigns the template [View] as the request response.
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source3" onclick="return koggle('error68b8f79f31e72source3')">SYSPATH/classes/Kohana/Controller.php [ 70 ]</a>
                    </span>
                    &raquo;
                    Kohana_Controller_Template->before()
                </p>
                <pre id="error68b8f79f31e72source3" class="source collapsed"><code><span class="line"><span class="number">65</span>      * @throws Kohana_HTTP_Exception
</span><span class="line"><span class="number">66</span>      */
</span><span class="line"><span class="number">67</span>     public function execute(): Response
</span><span class="line"><span class="number">68</span>     {
</span><span class="line"><span class="number">69</span>         // Execute the "before action" method
</span><span class="line highlight"><span class="number">70</span>         $this-&gt;before();
</span><span class="line"><span class="number">71</span>
</span><span class="line"><span class="number">72</span>         // Determine the action to use
</span><span class="line"><span class="number">73</span>         $action = 'action_' . $this-&gt;request-&gt;action();
</span><span class="line"><span class="number">74</span>
</span><span class="line"><span class="number">75</span>         // If the action doesn't exist, it's a 404
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        {PHP internal call}
                    </span>
                    &raquo;
                    Kohana_Controller->execute()
                </p>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source5" onclick="return koggle('error68b8f79f31e72source5')">SYSPATH/classes/Kohana/Request/Client/Internal.php [ 84 ]</a>
                    </span>
                    &raquo;
                    ReflectionMethod->invoke(<a href="#error68b8f79f31e72args5" onclick="return koggle('error68b8f79f31e72args5')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args5" class="collapsed">
                    <table>
                        <tr>
                            <td><code>object</code></td>
                            <td><pre><small>object</small> <span>Controller_Hello(4)</span> <code>{
    <small>public</small> template => <small>string</small><span>(4)</span> "site"
    <small>public</small> auto_render => <small>bool</small> TRUE
    <small>public</small> request => <small>object</small> <span>Request(19)</span> <code>{
        <small>protected</small> _requested_with => <small>NULL</small>
        <small>protected</small> _method => <small>string</small><span>(3)</span> "GET"
        <small>protected</small> _protocol => <small>string</small><span>(8)</span> "HTTP/1.1"
        <small>protected</small> _secure => <small>bool</small> FALSE
        <small>protected</small> _referrer => <small>NULL</small>
        <small>protected</small> _route => <small>object</small> <span>Route(5)</span> <code>{
            <small>protected</small> _filters => <small>array</small><span>(0)</span>
            <small>protected</small> _uri => <small>string</small><span>(32)</span> "(&lt;controller&gt;(/&lt;action&gt;(/&lt;id&gt;)))"
            <small>protected</small> _regex => <small>array</small><span>(0)</span>
            <small>protected</small> _defaults => <small>array</small><span>(2)</span> <span>(
                "controller" => <small>string</small><span>(7)</span> "welcome"
                "action" => <small>string</small><span>(5)</span> "index"
            )</span>
            <small>protected</small> _route_regex => <small>string</small><span>(95)</span> "#^(?:(?P&lt;controller&gt;[^/.,;?\n]++)(?:/(?P&lt;action&gt;[^/.,;?\n]++)(?:/(?P&lt;id&gt;[^/.,;?\n]++))?)?)?$#uD"
        }</code>
        <small>protected</small> _routes => <small>array</small><span>(0)</span>
        <small>protected</small> _header => <small>object</small> <span>HTTP_Header(0)</span> <code>{
        }</code>
        <small>protected</small> _body => <small>NULL</small>
        <small>protected</small> _directory => <small>string</small><span>(0)</span> ""
        <small>protected</small> _controller => <small>string</small><span>(5)</span> "Hello"
        <small>protected</small> _action => <small>string</small><span>(5)</span> "index"
        <small>protected</small> _uri => <small>string</small><span>(5)</span> "hello"
        <small>protected</small> _external => <small>bool</small> FALSE
        <small>protected</small> _params => <small>array</small><span>(0)</span>
        <small>protected</small> _get => <small>array</small><span>(0)</span>
        <small>protected</small> _post => <small>array</small><span>(0)</span>
        <small>protected</small> _cookies => <small>array</small><span>(5)</span> <span>(
            "_li_dcdm_c" => <small>NULL</small>
            "_lc2_fpi" => <small>NULL</small>
            "_lc2_fpi_js" => <small>NULL</small>
            "_li_ss" => <small>NULL</small>
            "session" => <small>NULL</small>
        )</span>
        <small>protected</small> _client => <small>object</small> <span>Request_Client_Internal(9)</span> <code>{
            <small>protected</small> _previous_environment => <small>NULL</small>
            <small>protected</small> _cache => <small>NULL</small>
            <small>protected</small> _follow => <small>bool</small> FALSE
            <small>protected</small> _follow_headers => <small>array</small><span>(1)</span> <span>(
                0 => <small>string</small><span>(13)</span> "authorization"
            )</span>
            <small>protected</small> _strict_redirect => <small>bool</small> TRUE
            <small>protected</small> _header_callbacks => <small>array</small><span>(1)</span> <span>(
                "Location" => <small>string</small><span>(34)</span> "Request_Client::on_header_location"
            )</span>
            <small>protected</small> _max_callback_depth => <small>integer</small> 5
            <small>protected</small> _callback_depth => <small>integer</small> 1
            <small>protected</small> _callback_params => <small>array</small><span>(0)</span>
        }</code>
    }</code>
    <small>public</small> response => <small>object</small> <span>Response(5)</span> <code>{
        <small>protected</small> _status => <small>integer</small> 200
        <small>protected</small> _header => <small>object</small> <span>HTTP_Header(0)</span> <code>{
        }</code>
        <small>protected</small> _body => <small>string</small><span>(0)</span> ""
        <small>protected</small> _cookies => <small>array</small><span>(0)</span>
        <small>protected</small> _protocol => <small>string</small><span>(8)</span> "HTTP/1.1"
    }</code>
}</code></pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source5" class="source collapsed"><code><span class="line"><span class="number">79</span>
</span><span class="line"><span class="number">80</span>             // Create a new instance of the controller
</span><span class="line"><span class="number">81</span>             $controller = $class-&gt;newInstance($request, $response);
</span><span class="line"><span class="number">82</span>
</span><span class="line"><span class="number">83</span>             // Run the controller's execute() method
</span><span class="line highlight"><span class="number">84</span>             $response = $class-&gt;getMethod('execute')-&gt;invoke($controller);
</span><span class="line"><span class="number">85</span>
</span><span class="line"><span class="number">86</span>             if (!$response instanceof Response) {
</span><span class="line"><span class="number">87</span>                 // Controller failed to return a Response.
</span><span class="line"><span class="number">88</span>                 throw new Kohana_Exception('Controller failed to return a Response');
</span><span class="line"><span class="number">89</span>             }
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source6" onclick="return koggle('error68b8f79f31e72source6')">SYSPATH/classes/Kohana/Request/Client.php [ 110 ]</a>
                    </span>
                    &raquo;
                    Kohana_Request_Client_Internal->execute_request(<a href="#error68b8f79f31e72args6" onclick="return koggle('error68b8f79f31e72args6')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args6" class="collapsed">
                    <table>
                        <tr>
                            <td><code>request</code></td>
                            <td><pre><small>object</small> <span>Request(19)</span> <code>{
    <small>protected</small> _requested_with => <small>NULL</small>
    <small>protected</small> _method => <small>string</small><span>(3)</span> "GET"
    <small>protected</small> _protocol => <small>string</small><span>(8)</span> "HTTP/1.1"
    <small>protected</small> _secure => <small>bool</small> FALSE
    <small>protected</small> _referrer => <small>NULL</small>
    <small>protected</small> _route => <small>object</small> <span>Route(5)</span> <code>{
        <small>protected</small> _filters => <small>array</small><span>(0)</span>
        <small>protected</small> _uri => <small>string</small><span>(32)</span> "(&lt;controller&gt;(/&lt;action&gt;(/&lt;id&gt;)))"
        <small>protected</small> _regex => <small>array</small><span>(0)</span>
        <small>protected</small> _defaults => <small>array</small><span>(2)</span> <span>(
            "controller" => <small>string</small><span>(7)</span> "welcome"
            "action" => <small>string</small><span>(5)</span> "index"
        )</span>
        <small>protected</small> _route_regex => <small>string</small><span>(95)</span> "#^(?:(?P&lt;controller&gt;[^/.,;?\n]++)(?:/(?P&lt;action&gt;[^/.,;?\n]++)(?:/(?P&lt;id&gt;[^/.,;?\n]++))?)?)?$#uD"
    }</code>
    <small>protected</small> _routes => <small>array</small><span>(0)</span>
    <small>protected</small> _header => <small>object</small> <span>HTTP_Header(0)</span> <code>{
    }</code>
    <small>protected</small> _body => <small>NULL</small>
    <small>protected</small> _directory => <small>string</small><span>(0)</span> ""
    <small>protected</small> _controller => <small>string</small><span>(5)</span> "Hello"
    <small>protected</small> _action => <small>string</small><span>(5)</span> "index"
    <small>protected</small> _uri => <small>string</small><span>(5)</span> "hello"
    <small>protected</small> _external => <small>bool</small> FALSE
    <small>protected</small> _params => <small>array</small><span>(0)</span>
    <small>protected</small> _get => <small>array</small><span>(0)</span>
    <small>protected</small> _post => <small>array</small><span>(0)</span>
    <small>protected</small> _cookies => <small>array</small><span>(5)</span> <span>(
        "_li_dcdm_c" => <small>NULL</small>
        "_lc2_fpi" => <small>NULL</small>
        "_lc2_fpi_js" => <small>NULL</small>
        "_li_ss" => <small>NULL</small>
        "session" => <small>NULL</small>
    )</span>
    <small>protected</small> _client => <small>object</small> <span>Request_Client_Internal(9)</span> <code>{
        <small>protected</small> _previous_environment => <small>NULL</small>
        <small>protected</small> _cache => <small>NULL</small>
        <small>protected</small> _follow => <small>bool</small> FALSE
        <small>protected</small> _follow_headers => <small>array</small><span>(1)</span> <span>(
            0 => <small>string</small><span>(13)</span> "authorization"
        )</span>
        <small>protected</small> _strict_redirect => <small>bool</small> TRUE
        <small>protected</small> _header_callbacks => <small>array</small><span>(1)</span> <span>(
            "Location" => <small>string</small><span>(34)</span> "Request_Client::on_header_location"
        )</span>
        <small>protected</small> _max_callback_depth => <small>integer</small> 5
        <small>protected</small> _callback_depth => <small>integer</small> 1
        <small>protected</small> _callback_params => <small>array</small><span>(0)</span>
    }</code>
}</code></pre></td>
                        </tr>
                        <tr>
                            <td><code>response</code></td>
                            <td><pre><small>object</small> <span>Response(5)</span> <code>{
    <small>protected</small> _status => <small>integer</small> 200
    <small>protected</small> _header => <small>object</small> <span>HTTP_Header(0)</span> <code>{
    }</code>
    <small>protected</small> _body => <small>string</small><span>(0)</span> ""
    <small>protected</small> _cookies => <small>array</small><span>(0)</span>
    <small>protected</small> _protocol => <small>string</small><span>(8)</span> "HTTP/1.1"
}</code></pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source6" class="source collapsed"><code><span class="line"><span class="number">105</span>         $orig_response = $response = Response::factory(['_protocol' =&gt; $request-&gt;protocol()]);
</span><span class="line"><span class="number">106</span>
</span><span class="line"><span class="number">107</span>         if (($cache = $this-&gt;cache()) instanceof HTTP_Cache)
</span><span class="line"><span class="number">108</span>             return $cache-&gt;execute($this, $request, $response);
</span><span class="line"><span class="number">109</span>
</span><span class="line highlight"><span class="number">110</span>         $response = $this-&gt;execute_request($request, $response);
</span><span class="line"><span class="number">111</span>
</span><span class="line"><span class="number">112</span>         // Execute response callbacks
</span><span class="line"><span class="number">113</span>         foreach ($this-&gt;header_callbacks() as $header =&gt; $callback) {
</span><span class="line"><span class="number">114</span>             if ($response-&gt;headers($header)) {
</span><span class="line"><span class="number">115</span>                 $cb_result = call_user_func($callback, $request, $response, $this);
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source7" onclick="return koggle('error68b8f79f31e72source7')">SYSPATH/classes/Kohana/Request.php [ 822 ]</a>
                    </span>
                    &raquo;
                    Kohana_Request_Client->execute(<a href="#error68b8f79f31e72args7" onclick="return koggle('error68b8f79f31e72args7')">arguments</a>)
                </p>
                <div id="error68b8f79f31e72args7" class="collapsed">
                    <table>
                        <tr>
                            <td><code>request</code></td>
                            <td><pre><small>object</small> <span>Request(19)</span> <code>{
    <small>protected</small> _requested_with => <small>NULL</small>
    <small>protected</small> _method => <small>string</small><span>(3)</span> "GET"
    <small>protected</small> _protocol => <small>string</small><span>(8)</span> "HTTP/1.1"
    <small>protected</small> _secure => <small>bool</small> FALSE
    <small>protected</small> _referrer => <small>NULL</small>
    <small>protected</small> _route => <small>object</small> <span>Route(5)</span> <code>{
        <small>protected</small> _filters => <small>array</small><span>(0)</span>
        <small>protected</small> _uri => <small>string</small><span>(32)</span> "(&lt;controller&gt;(/&lt;action&gt;(/&lt;id&gt;)))"
        <small>protected</small> _regex => <small>array</small><span>(0)</span>
        <small>protected</small> _defaults => <small>array</small><span>(2)</span> <span>(
            "controller" => <small>string</small><span>(7)</span> "welcome"
            "action" => <small>string</small><span>(5)</span> "index"
        )</span>
        <small>protected</small> _route_regex => <small>string</small><span>(95)</span> "#^(?:(?P&lt;controller&gt;[^/.,;?\n]++)(?:/(?P&lt;action&gt;[^/.,;?\n]++)(?:/(?P&lt;id&gt;[^/.,;?\n]++))?)?)?$#uD"
    }</code>
    <small>protected</small> _routes => <small>array</small><span>(0)</span>
    <small>protected</small> _header => <small>object</small> <span>HTTP_Header(0)</span> <code>{
    }</code>
    <small>protected</small> _body => <small>NULL</small>
    <small>protected</small> _directory => <small>string</small><span>(0)</span> ""
    <small>protected</small> _controller => <small>string</small><span>(5)</span> "Hello"
    <small>protected</small> _action => <small>string</small><span>(5)</span> "index"
    <small>protected</small> _uri => <small>string</small><span>(5)</span> "hello"
    <small>protected</small> _external => <small>bool</small> FALSE
    <small>protected</small> _params => <small>array</small><span>(0)</span>
    <small>protected</small> _get => <small>array</small><span>(0)</span>
    <small>protected</small> _post => <small>array</small><span>(0)</span>
    <small>protected</small> _cookies => <small>array</small><span>(5)</span> <span>(
        "_li_dcdm_c" => <small>NULL</small>
        "_lc2_fpi" => <small>NULL</small>
        "_lc2_fpi_js" => <small>NULL</small>
        "_li_ss" => <small>NULL</small>
        "session" => <small>NULL</small>
    )</span>
    <small>protected</small> _client => <small>object</small> <span>Request_Client_Internal(9)</span> <code>{
        <small>protected</small> _previous_environment => <small>NULL</small>
        <small>protected</small> _cache => <small>NULL</small>
        <small>protected</small> _follow => <small>bool</small> FALSE
        <small>protected</small> _follow_headers => <small>array</small><span>(1)</span> <span>(
            0 => <small>string</small><span>(13)</span> "authorization"
        )</span>
        <small>protected</small> _strict_redirect => <small>bool</small> TRUE
        <small>protected</small> _header_callbacks => <small>array</small><span>(1)</span> <span>(
            "Location" => <small>string</small><span>(34)</span> "Request_Client::on_header_location"
        )</span>
        <small>protected</small> _max_callback_depth => <small>integer</small> 5
        <small>protected</small> _callback_depth => <small>integer</small> 1
        <small>protected</small> _callback_params => <small>array</small><span>(0)</span>
    }</code>
}</code></pre></td>
                        </tr>
                    </table>
                </div>
                <pre id="error68b8f79f31e72source7" class="source collapsed"><code><span class="line"><span class="number">817</span>
</span><span class="line"><span class="number">818</span>         if (!$this-&gt;_client instanceof Request_Client) {
</span><span class="line"><span class="number">819</span>             throw new Request_Exception('Unable to execute :uri without a Kohana_Request_Client', [':uri' =&gt; $this-&gt;_uri]);
</span><span class="line"><span class="number">820</span>         }
</span><span class="line"><span class="number">821</span>
</span><span class="line highlight"><span class="number">822</span>         return $this-&gt;_client-&gt;execute($this);
</span><span class="line"><span class="number">823</span>     }
</span><span class="line"><span class="number">824</span>
</span><span class="line"><span class="number">825</span>     /**
</span><span class="line"><span class="number">826</span>      * Returns whether this request is the initial request Kohana received.
</span><span class="line"><span class="number">827</span>      * Can be used to test for sub requests.
</span></code></pre>
            </li>
            <li>
                <p>
                    <span class="file">
                        <a href="#error68b8f79f31e72source8" onclick="return koggle('error68b8f79f31e72source8')">DOCROOT/index.php [ 64 ]</a>
                    </span>
                    &raquo;
                    Kohana_Request->execute()
                </p>
                <pre id="error68b8f79f31e72source8" class="source collapsed"><code><span class="line"><span class="number">59</span>     /**
</span><span class="line"><span class="number">60</span>      * Execute the main request. A source of the URI can be passed, e.g., $_SERVER['PATH_INFO'].
</span><span class="line"><span class="number">61</span>      * If no source is specified, the URI will be automatically detected.
</span><span class="line"><span class="number">62</span>      */
</span><span class="line"><span class="number">63</span>     echo Request::factory('', [], false)
</span><span class="line highlight"><span class="number">64</span>         -&gt;execute()
</span><span class="line"><span class="number">65</span>         -&gt;send_headers(true)
</span><span class="line"><span class="number">66</span>         -&gt;body();
</span><span class="line"><span class="number">67</span> }
</span></code></pre>
            </li>
        </ol>
    </div>
    <h2><a href="#error68b8f79f31e72environment" onclick="return koggle('error68b8f79f31e72environment')">Environment</a></h2>
    <div id="error68b8f79f31e72environment" class="content collapsed">
        <h3><a href="#error68b8f79f31e72environment_included" onclick="return koggle('error68b8f79f31e72environment_included')">Included files</a> (72)</h3>
        <div id="error68b8f79f31e72environment_included" class="collapsed">
            <table>
                <tr>
                    <td><code>DOCROOT/index.php</code></td>
                </tr>
                <tr>
                    <td><code>APPPATH/bootstrap.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/autoload.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/composer/autoload_real.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/composer/platform_check.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/composer/ClassLoader.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/composer/autoload_static.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/symfony/polyfill-ctype/bootstrap.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/symfony/polyfill-php80/bootstrap.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/myclabs/deep-copy/src/DeepCopy/deep_copy.php</code></td>
                </tr>
                <tr>
                    <td><code>VENDOR_PATH/symfony/polyfill-mbstring/bootstrap.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Core.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/I18n.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/I18n.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/HTTP.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/HTTP.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Exception.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Kohana/Exception.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Log.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Log.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Config.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Config.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Log/File.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Log/File.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Log/Writer.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Log/Writer.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Config/File.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Config/File.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Config/File/Reader.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Config/Reader.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Config/Source.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Cookie.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Cookie.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Route.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Route.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Request.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Request.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/HTTP/Request.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/HTTP/Request.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/HTTP/Message.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/HTTP/Message.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/HTTP/Header.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/HTTP/Header.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Request/Client/Internal.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Request/Client/Internal.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Request/Client.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Request/Client.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Arr.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Arr.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Response.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Response.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/HTTP/Response.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/HTTP/Response.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Profiler.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Profiler.php</code></td>
                </tr>
                <tr>
                    <td><code>APPPATH/classes/Controller/Hello.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Controller/Template.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Controller/Template.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Controller.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Controller.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/View.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/View.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/View/Exception.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/View/Exception.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Debug.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Debug.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Date.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/Date.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/views/kohana/error.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/UTF8.php</code></td>
                </tr>
                <tr>
                    <td><code>SYSPATH/classes/Kohana/UTF8.php</code></td>
                </tr>
            </table>
        </div>
        <h3><a href="#error4ac2453378034environment_loaded" onclick="return koggle('error4ac2453378034environment_loaded')">Loaded extensions</a> (41)</h3>
        <div id="error4ac2453378034environment_loaded" class="collapsed">
            <table>
                <tr>
                    <td><code>zip</code></td>
                </tr>
                <tr>
                    <td><code>xmlwriter</code></td>
                </tr>
                <tr>
                    <td><code>libxml</code></td>
                </tr>
                <tr>
                    <td><code>xml</code></td>
                </tr>
                <tr>
                    <td><code>wddx</code></td>
                </tr>
                <tr>
                    <td><code>tokenizer</code></td>
                </tr>
                <tr>
                    <td><code>sysvshm</code></td>
                </tr>
                <tr>
                    <td><code>sysvsem</code></td>
                </tr>
                <tr>
                    <td><code>sysvmsg</code></td>
                </tr>
                <tr>
                    <td><code>session</code></td>
                </tr>
                <tr>
                    <td><code>SimpleXML</code></td>
                </tr>
                <tr>
                    <td><code>sockets</code></td>
                </tr>
                <tr>
                    <td><code>soap</code></td>
                </tr>
                <tr>
                    <td><code>SPL</code></td>
                </tr>
                <tr>
                    <td><code>shmop</code></td>
                </tr>
                <tr>
                    <td><code>standard</code></td>
                </tr>
                <tr>
                    <td><code>Reflection</code></td>
                </tr>
                <tr>
                    <td><code>posix</code></td>
                </tr>
                <tr>
                    <td><code>mime_magic</code></td>
                </tr>
                <tr>
                    <td><code>mbstring</code></td>
                </tr>
                <tr>
                    <td><code>json</code></td>
                </tr>
                <tr>
                    <td><code>iconv</code></td>
                </tr>
                <tr>
                    <td><code>hash</code></td>
                </tr>
                <tr>
                    <td><code>gettext</code></td>
                </tr>
                <tr>
                    <td><code>ftp</code></td>
                </tr>
                <tr>
                    <td><code>filter</code></td>
                </tr>
                <tr>
                    <td><code>exif</code></td>
                </tr>
                <tr>
                    <td><code>dom</code></td>
                </tr>
                <tr>
                    <td><code>dba</code></td>
                </tr>
                <tr>
                    <td><code>date</code></td>
                </tr>
                <tr>
                    <td><code>ctype</code></td>
                </tr>
                <tr>
                    <td><code>calendar</code></td>
                </tr>
                <tr>
                    <td><code>bz2</code></td>
                </tr>
                <tr>
                    <td><code>bcmath</code></td>
                </tr>
                <tr>
                    <td><code>zlib</code></td>
                </tr>
                <tr>
                    <td><code>pcre</code></td>
                </tr>
                <tr>
                    <td><code>openssl</code></td>
                </tr>
                <tr>
                    <td><code>xmlreader</code></td>
                </tr>
                <tr>
                    <td><code>apache2handler</code></td>
                </tr>
                <tr>
                    <td><code>curl</code></td>
                </tr>
                <tr>
                    <td><code>PDO</code></td>
                </tr>
            </table>
        </div>
        <h3><a href="#error68b902f432065environment_server" onclick="return koggle('error68b902f432065environment_server')">$_SERVER</a></h3>
        <div id="error68b902f432065environment_server" class="collapsed">
            <table>
                <tr>
                    <td><code>USER</code></td>
                    <td><pre><small>string</small><span>(8)</span> "www-data"</pre></td>
                </tr>
                <tr>
                    <td><code>HOME</code></td>
                    <td><pre><small>string</small><span>(8)</span> "/var/www"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_ACCEPT_LANGUAGE</code></td>
                    <td><pre><small>string</small><span>(47)</span> "en-GB,en-US;q=0.9,en;q=0.8"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_ACCEPT_ENCODING</code></td>
                    <td><pre><small>string</small><span>(13)</span> "gzip, deflate"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_ACCEPT</code></td>
                    <td><pre><small>string</small><span>(135)</span> "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b&nbsp;&hellip;"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_USER_AGENT</code></td>
                    <td><pre><small>string</small><span>(117)</span> "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_UPGRADE_INSECURE_REQUESTS</code></td>
                    <td><pre><small>string</small><span>(1)</span> "1"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_CONNECTION</code></td>
                    <td><pre><small>string</small><span>(10)</span> "keep-alive"</pre></td>
                </tr>
                <tr>
                    <td><code>HTTP_HOST</code></td>
                    <td><pre><small>string</small><span>(9)</span> "localhost"</pre></td>
                </tr>
                <tr>
                    <td><code>REDIRECT_STATUS</code></td>
                    <td><pre><small>string</small><span>(3)</span> "200"</pre></td>
                </tr>
                <tr>
                    <td><code>SERVER_NAME</code></td>
                    <td><pre><small>string</small><span>(9)</span> "localhost"</pre></td>
                </tr>
                <tr>
                    <td><code>SERVER_PORT</code></td>
                    <td><pre><small>string</small><span>(2)</span> "80"</pre></td>
                </tr>
                <tr>
                    <td><code>SERVER_ADDR</code></td>
                    <td><pre><small>string</small><span>(9)</span> "127.0.0.1"</pre></td>
                </tr>
                <tr>
                    <td><code>REMOTE_PORT</code></td>
                    <td><pre><small>string</small><span>(5)</span> "64015"</pre></td>
                </tr>
                <tr>
                    <td><code>REMOTE_ADDR</code></td>
                    <td><pre><small>string</small><span>(9)</span> "127.0.0.1"</pre></td>
                </tr>
                <tr>
                    <td><code>SERVER_SOFTWARE</code></td>
                    <td><pre><small>string</small><span>(12)</span> "nginx/1.27.4"</pre></td>
                </tr>
                <tr>
                    <td><code>GATEWAY_INTERFACE</code></td>
                    <td><pre><small>string</small><span>(7)</span> "CGI/1.1"</pre></td>
                </tr>
                <tr>
                    <td><code>REQUEST_SCHEME</code></td>
                    <td><pre><small>string</small><span>(4)</span> "http"</pre></td>
                </tr>
                <tr>
                    <td><code>SERVER_PROTOCOL</code></td>
                    <td><pre><small>string</small><span>(8)</span> "HTTP/1.1"</pre></td>
                </tr>
                <tr>
                    <td><code>DOCUMENT_ROOT</code></td>
                    <td><pre><small>string</small><span>(40)</span> "/var/www/html/kohana/public"</pre></td>
                </tr>
                <tr>
                    <td><code>DOCUMENT_URI</code></td>
                    <td><pre><small>string</small><span>(10)</span> "/index.php"</pre></td>
                </tr>
                <tr>
                    <td><code>REQUEST_URI</code></td>
                    <td><pre><small>string</small><span>(16)</span> "/index.php/hello"</pre></td>
                </tr>
                <tr>
                    <td><code>SCRIPT_NAME</code></td>
                    <td><pre><small>string</small><span>(10)</span> "/index.php"</pre></td>
                </tr>
                <tr>
                    <td><code>CONTENT_LENGTH</code></td>
                    <td><pre><small>string</small><span>(0)</span> ""</pre></td>
                </tr>
                <tr>
                    <td><code>CONTENT_TYPE</code></td>
                    <td><pre><small>string</small><span>(0)</span> ""</pre></td>
                </tr>
                <tr>
                    <td><code>REQUEST_METHOD</code></td>
                    <td><pre><small>string</small><span>(3)</span> "GET"</pre></td>
                </tr>
                <tr>
                    <td><code>QUERY_STRING</code></td>
                    <td><pre><small>string</small><span>(0)</span> ""</pre></td>
                </tr>
                <tr>
                    <td><code>SCRIPT_FILENAME</code></td>
                    <td><pre><small>string</small><span>(21)</span> "/srv/public/index.php"</pre></td>
                </tr>
                <tr>
                    <td><code>PATH_INFO</code></td>
                    <td><pre><small>string</small><span>(0)</span> ""</pre></td>
                </tr>
                <tr>
                    <td><code>FCGI_ROLE</code></td>
                    <td><pre><small>string</small><span>(9)</span> "RESPONDER"</pre></td>
                </tr>
                <tr>
                    <td><code>PHP_SELF</code></td>
                    <td><pre><small>string</small><span>(10)</span> "/index.php"</pre></td>
                </tr>
                <tr>
                    <td><code>REQUEST_TIME_FLOAT</code></td>
                    <td><pre><small>float</small> 1756955380.1472</pre></td>
                </tr>
                <tr>
                    <td><code>REQUEST_TIME</code></td>
                    <td><pre><small>integer</small> 1756955380</pre></td>
                </tr>
            </table>
        </div>
    </div>
</div>
