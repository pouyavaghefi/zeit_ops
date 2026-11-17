<div id="top-bar">
    <div class="container clearfix">
        <div id="top-menu">
            <ul>
                <li class="top-menu-em dropdown">
                    @guest
                        <a href="{{ route('login') }}">
                            <i class="fa fa-user"></i> Client Area
                        </a>
                    @else
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/panel/dashboard">
                                    <i class="fas fa-user-circle"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/logout">
                                    <i class="fas fa-user-circle"></i> Logout
                                </a>
                            </li>
                        </ul>
                    @endguest
                </li>
                <li><span>/</span><a href="/contact">Contact</a></li>

                {{-- ZeitOps Language Selector --}}
                <li>
                    <span>/</span>
                    <a href="{{ url('/') }}">
                        English
                        <i class="fa fa-angle-down norightmargin"></i>
                    </a>

                    <ul class="language-dropdown">

                        {{-- English --}}
                        <li>
                            <a href="{{ url('/') }}">
                                <img src="https://flagcdn.com/w20/gb.png" alt="English">
                                English
                            </a>
                        </li>

                        {{-- German --}}
                        <li>
                            <a href="{{ url('/de') }}">
                                <img src="https://flagcdn.com/w20/de.png" alt="Deutsch">
                                Deutsch
                            </a>
                        </li>

                        {{-- Dutch --}}
                        <li>
                            <a href="{{ url('/nl') }}">
                                <img src="https://flagcdn.com/w20/nl.png" alt="Nederlands">
                                Nederlands
                            </a>
                        </li>

                        {{-- French --}}
                        <li>
                            <a href="{{ url('/fr') }}">
                                <img src="https://flagcdn.com/w20/fr.png" alt="Français">
                                Français
                            </a>
                        </li>

                        {{-- Italian --}}
                        <li>
                            <a href="{{ url('/it') }}">
                                <img src="https://flagcdn.com/w20/it.png" alt="Italiano">
                                Italiano
                            </a>
                        </li>

                        {{-- Portuguese --}}
                        <li>
                            <a href="{{ url('/pt') }}">
                                <img src="https://flagcdn.com/w20/pt.png" alt="Português">
                                Português
                            </a>
                        </li>

                        {{-- Spanish --}}
                        <li>
                            <a href="{{ url('/es') }}">
                                <img src="https://flagcdn.com/w20/es.png" alt="Español">
                                Español
                            </a>
                        </li>

                        {{-- Norwegian --}}
                        <li>
                            <a href="{{ url('/nb') }}">
                                <img src="https://flagcdn.com/w20/no.png" alt="Norsk">
                                Norsk
                            </a>
                        </li>

                        {{-- Turkish --}}
                        <li>
                            <a href="{{ url('/tr') }}">
                                <img src="https://flagcdn.com/w20/tr.png" alt="Türkçe">
                                Türkçe
                            </a>
                        </li>

                        {{-- Polish --}}
                        <li>
                            <a href="{{ url('/pl') }}">
                                <img src="https://flagcdn.com/w20/pl.png" alt="Polski">
                                Polski
                            </a>
                        </li>

                        {{-- Danish --}}
                        <li>
                            <a href="{{ url('/da') }}">
                                <img src="https://flagcdn.com/w20/dk.png" alt="Dansk">
                                Dansk
                            </a>
                        </li>

                        {{-- Finnish --}}
                        <li>
                            <a href="{{ url('/fi') }}">
                                <img src="https://flagcdn.com/w20/fi.png" alt="Suomi">
                                Suomi
                            </a>
                        </li>

                        {{-- Czech --}}
                        <li>
                            <a href="{{ url('/cs') }}">
                                <img src="https://flagcdn.com/w20/cz.png" alt="Čeština">
                                Čeština
                            </a>
                        </li>

                        {{-- Ukrainian --}}
                        <li>
                            <a href="{{ url('/uk') }}">
                                <img src="https://flagcdn.com/w20/ua.png" alt="Українська">
                                Українська
                            </a>
                        </li>

                        {{-- Greek --}}
                        <li>
                            <a href="{{ url('/el') }}">
                                <img src="https://flagcdn.com/w20/gr.png" alt="Ελληνικά">
                                Ελληνικά
                            </a>
                        </li>

                        {{-- Hungarian --}}
                        <li>
                            <a href="{{ url('/hu') }}">
                                <img src="https://flagcdn.com/w20/hu.png" alt="Magyar">
                                Magyar
                            </a>
                        </li>

                        {{-- Swedish --}}
                        <li>
                            <a href="{{ url('/sv') }}">
                                <img src="https://flagcdn.com/w20/se.png" alt="Svenska">
                                Svenska
                            </a>
                        </li>

                        {{-- Slovak --}}
                        <li>
                            <a href="{{ url('/sk') }}">
                                <img src="https://flagcdn.com/w20/sk.png" alt="Slovenčina">
                                Slovenčina
                            </a>
                        </li>

                        {{-- Romanian --}}
                        <li>
                            <a href="{{ url('/ro') }}">
                                <img src="https://flagcdn.com/w20/ro.png" alt="Română">
                                Română
                            </a>
                        </li>

                        {{-- Lithuanian --}}
                        <li>
                            <a href="{{ url('/lt') }}">
                                <img src="https://flagcdn.com/w20/lt.png" alt="Lietuvių">
                                Lietuvių
                            </a>
                        </li>

                    </ul>
                </li>

                <li><span>/</span><a href="/">Home</a></li>
            </ul>
        </div>

        <div id="top-social">
            <ul>
                <li class="ts-youtube"><a href="#"><div class="ts-icon"></div><div class="ts-text">YouTube</div></a></li>
                <li class="ts-facebook"><a href="#"><div class="ts-icon"></div><div class="ts-text">Facebook</div></a></li>
                <li class="ts-rss"><a href="#"><div class="ts-icon"></div><div class="ts-text">RSS</div></a></li>
                <li class="ts-phone"><a href="tel:+9134336129"><div class="ts-icon"></div><div class="ts-text" style="color:white !important">+98-912-834-7349 (Telegram)</div></a></li>
                <li class="ts-mail"><a href="mailto:info@pouyait.com"><div class="ts-icon"></div><div class="ts-text">info@hackalone.org</div></a></li>
            </ul>
        </div>
    </div>
</div>
