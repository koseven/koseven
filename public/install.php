<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Koseven Installation</title>
    <meta name="title" content="Koseven Installation">
    <meta name="description" content="The following tests have been run to determine if Koseven will work in your environment.">
    <meta name="author" content="Koseven Team">
    <style>
        body {
            margin: 0;
            background-color: #efefef;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 10px auto;
            background: #fff;
            padding: 20px 15px;
            border-radius: 12px;
        }

        table {
            margin: 20px 0;
        }

        h1,
        th {
            margin: 5px 0;
            color: #2f2f2f;
        }

        p,
        td {
            margin: 10px 0;
            color: #383838;
        }

        a,
        a:visited {
            color: #3195f0;
            text-decoration: none;
        }

        th {
            text-align: left;
            width: 30%;
        }

        td,
        th {
            padding: 5px 0;
        }

        .logo {
            background-image: url(data:image/png;base64,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);
            width: 80px;
            height: 40px;
            float: right;
        }

        .pass {
            color: #155724;
        }

        .fail {
            color: #721c24;
        }

        code {
            font-family: monospace;
            font-size: 15px;
            background: #f1f0f0;
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            border: 1px solid;
            margin-bottom: 20px;
        }

        .alert code {
            background: none;
        }

        .danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-bottom: -6px;
            margin-right: 10px;
        }

        .icon-success {
            background-image: url(data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjMTU1NzI0IiBoZWlnaHQ9IjI0IiB2aWV3Qm94PSIwIDAgMjQgMjQiIHdpZHRoPSIyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4gICAgPHBhdGggZD0iTTAgMGgyNHYyNEgweiIgZmlsbD0ibm9uZSIvPiAgICA8cGF0aCBkPSJNOSAxNi4yTDQuOCAxMmwtMS40IDEuNEw5IDE5IDIxIDdsLTEuNC0xLjRMOSAxNi4yeiIvPjwvc3ZnPg==);
        }

        .icon-danger {
            background-image: url(data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjNzIxYzI0IiBoZWlnaHQ9IjI0IiB2aWV3Qm94PSIwIDAgMjQgMjQiIHdpZHRoPSIyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4gICAgPHBhdGggZD0iTTE5IDYuNDFMMTcuNTkgNSAxMiAxMC41OSA2LjQxIDUgNSA2LjQxIDEwLjU5IDEyIDUgMTcuNTkgNi40MSAxOSAxMiAxMy40MSAxNy41OSAxOSAxOSAxNy41OSAxMy40MSAxMnoiLz4gICAgPHBhdGggZD0iTTAgMGgyNHYyNEgweiIgZmlsbD0ibm9uZSIvPjwvc3ZnPg==);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo"></div>

        <h1>Environment Tests</h1>
        <p>
            The following tests have been run to determine if Koseven will work in your environment. If any of the tests have failed, consult the <a href="https://docs.koseven.ga/guide/kohana/install" target="_blank">documentation</a> for more information on how to correct the problem.
        </p>

        <?php $failed = FALSE ?>

        <table>
            <tbody>
                <tr>
                    <th>PHP Version</th>
                    <?php if (version_compare(PHP_VERSION, '7', '>=')): ?>
                    <td class="pass">
                        <?php echo PHP_VERSION ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">Koseven requires PHP 7 or newer, this version is
                        <?php echo PHP_VERSION ?>.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>System Directory</th>
                    <?php if (is_dir(SYSPATH) AND is_file(SYSPATH.'classes/Kohana'.EXT)): ?>
                    <td class="pass">
                        <?php echo SYSPATH ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Application Directory</th>
                    <?php if (is_dir(APPPATH) AND is_file(APPPATH.'bootstrap'.EXT)): ?>
                    <td class="pass">
                        <?php echo APPPATH ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Cache Directory</th>
                    <?php if (is_dir(APPPATH) AND is_dir(APPPATH.'cache') AND is_writable(APPPATH.'cache')): ?>
                    <td class="pass">
                        <?php echo APPPATH.'cache/' ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <code><?php echo APPPATH.'cache/' ?></code> directory is not writable.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Logs Directory</th>
                    <?php if (is_dir(APPPATH) AND is_dir(APPPATH.'logs') AND is_writable(APPPATH.'logs')): ?>
                    <td class="pass">
                        <?php echo APPPATH.'logs/' ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <code><?php echo APPPATH.'logs/' ?></code> directory is not writable.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>PCRE UTF-8</th>
                    <?php if ( ! @preg_match('/^.$/u', 'ñ')): $failed = TRUE ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
                    <?php elseif ( ! @preg_match('/^\pL$/u', 'ñ')): $failed = TRUE ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>SPL Enabled</th>
                    <?php if (function_exists('spl_autoload_register')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">PHP <a href="http://www.php.net/spl">SPL</a> is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Reflection Enabled</th>
                    <?php if (class_exists('ReflectionClass')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Filters Enabled</th>
                    <?php if (function_exists('filter_list')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Iconv Extension Loaded</th>
                    <?php if (extension_loaded('iconv')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
                    <?php endif ?>
                </tr>
                <?php if (extension_loaded('mbstring')): ?>
                <tr>
                    <th>Mbstring Not Overloaded</th>
                    <?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <?php endif ?>
                <tr>
                    <th>Character Type (CTYPE) Extension</th>
                    <?php if ( ! function_exists('ctype_digit')): $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>URI Determination</th>
                    <?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF']) OR isset($_SERVER['PATH_INFO'])): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>, or <code>$_SERVER['PATH_INFO']</code> is available.</td>
                    <?php endif ?>
                </tr>
            </tbody>
        </table>

        <?php if ($failed === TRUE): ?>
        <div id="results" class="alert danger">
            <span class="icon icon-danger"></span> Koseven may not work correctly with your environment.
        </div>
        <?php else: ?>
        <div id="results" class="alert success">
            <span class="icon icon-success"></span> Your environment passed all requirements. Remove or rename the <code>install<?php echo EXT ?></code> file now.
        </div>
        <?php endif ?>

        <h1>Optional Tests</h1>

        <p>
            The following extensions are not required to run the Koseven core, but if enabled can provide access to additional classes.
        </p>

        <table>
            <tbody>
                <tr>
                    <th>PECL HTTP Enabled</th>
                    <?php if (extension_loaded('http')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven can use the <a href="http://php.net/http">http</a> extension for the Request_Client_External class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>cURL Enabled</th>
                    <?php if (extension_loaded('curl')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven can use the <a href="http://php.net/curl">cURL</a> extension for the Request_Client_External class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>mcrypt Enabled</th>
                    <?php if (extension_loaded('mcrypt')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven requires <a href="http://php.net/mcrypt">mcrypt</a> for the Encrypt class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>GD Enabled</th>
                    <?php if (function_exists('gd_info')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven requires <a href="http://php.net/gd">GD</a> v2 for the Image class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>MySQLi Enabled</th>
                    <?php if (function_exists('mysqli_connect')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven can use the <a href="http://php.net/mysqli">MySQLi</a> extension to support MySQL databases.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>PDO Enabled</th>
                    <?php if (class_exists('PDO')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Koseven can use <a href="http://php.net/pdo">PDO</a> to support additional databases.</td>
                    <?php endif ?>
                </tr>
            </tbody>
        </table>

    </div>
</body>

</html>
