<div class="col_one_third col_last">
    <div class="well nobottommargin" data-animate="wobble">
        <h3>Connect Your Server with <span style="color: #007bff; font-weight: 700;">ZeitOps</span></h3>

        <form action="/servers/new" method="get" id="server-info-form" name="server-info-form" class="nobottommargin">
            <div class="col_half nobottommargin">
                <label for="server-ip">Server IP</label>
                <input class="input-block-level" id="server-ip" name="ip" type="text" placeholder="Server IP" value="{{ request('ip') }}" />
            </div>

            <div class="col_half nobottommargin col_last">
                <label for="server-port">Server Port</label>
                <input class="input-block-level" id="server-port" name="port" type="text" placeholder="Server Port" value="{{ request('port') }}" />
            </div>

            <div class="clear"></div>

            <div class="col_half nobottommargin">
                <label for="root-username">Root Username</label>
                <input class="input-block-level" id="root-username" name="username" type="text" placeholder="Root Username" value="{{ request('username') }}" />
            </div>

            <div class="col_half nobottommargin col_last">
                <label for="root-password">Root Password</label>
                <input class="input-block-level" id="root-password" name="password" type="password" placeholder="Root Password" value="{{ request('password') }}" />
            </div>

            <div class="clear"></div>

            <div class="col_full center nobottommargin">
                <button class="btn btn-default" id="server-form-submit" name="server-form-submit" type="submit" value="submit">Submit</button>
            </div>
        </form>

    </div>
</div>
