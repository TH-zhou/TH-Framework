<!DOCTYPE html>
<html>
<head>
    <title>TH Framwork</title>
    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: table;
            font-weight: 100;
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .line {
            color: black;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <span class="line"><b>string1的值为：{$string1}</b></span>
        <br />
        <span class="line"><b>string2的值为：{$string2}</b></span>
        <br/>
        <span class="line"><b>
        {if $bool}
            结果为真
        {else}
            结果为假
        {/if}
        </b></span><br/>
        {foreach $array as key => value}
        <span class="line">{@key}....{@value}</span> <br />
        {/foreach}
        {#}我是注释，看不见我看不见我{/#}<br/>
    </div>
</div>
</body>
</html>