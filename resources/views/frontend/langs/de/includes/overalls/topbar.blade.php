<div id="top-bar">
    <div class="container clearfix">
        <div id="top-menu">
            <ul>
                <li class="top-menu-em dropdown">
                    @guest
                        <a href="{{ route('login') }}">
                            <i class="fa fa-user"></i> Kundenbereich
                        </a>
                    @else
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/panel/dashboard">
                                    <i class="fas fa-user-circle"></i> Bedienfeld
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/logout">
                                    <i class="fas fa-user-circle"></i> Abmelden
                                </a>
                            </li>
                        </ul>
                    @endguest
                </li>
                <li><span>/</span><a href="/contact">Kontakt</a></li>
                <li><span>/</span><a href="{{ env('APP_URL') }}/de">DE <i class="fa fa-angle-down norightmargin"></i></a>
                    <ul>
                        <li><a href="{{ env('APP_URL') }}/nl">
                                <img src="https://flagcdn.com/w20/nl.png" alt="Niederländisch" title="Niederländisch" /> Dutch
                            </a></li>
                        <li><a href="{{ env('APP_URL') }}/en">
                                <img src="https://flagcdn.com/w20/gb.png" alt="Englisch" title="Englisch" /> English
                            </a></li>
                    </ul>
                </li>
                <li><span>/</span><a href="/">Startseite</a></li>
            </ul>
        </div>

        <div id="top-social">
            <ul>
                <li class="ts-rss"><a href="#"><div class="ts-icon"></div><div class="ts-text">RSS</div></a></li>
                <li class="ts-phone"><a href="tel:+9134336129"><div class="ts-icon"></div><div class="ts-text" style="color:white !important">+98-912-834-7349 (Telegram)</div></a></li>
                <li class="ts-mail"><a href="mailto:info@pouyait.com"><div class="ts-icon"></div><div class="ts-text">info@hackalone.org</div></a></li>
            </ul>
        </div>
    </div>
</div>
