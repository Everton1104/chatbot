<div class="side-menu">
    {{-- CHAT --}}
    <div class="menu-item" onclick="window.location.href='/chat'">
        CHAT
    </div>

    {{-- LOGOUT --}}
    <div class="menu-item" onclick="$('#logout-form').submit()">
        SAIR
        <form id="logout-form" action="/logout" method="POST">
            @csrf
            @method('POST')
        </form>
    </div>
</div>

{{-- Icones e Backdrop --}}
<svg class="menu-icon" id="menu-bars" height="2rem" viewBox="0 -960 960 960" width="2rem" fill="#d2d2d2"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg>
<svg class="menu-icon d-none" id="menu-times" height="2rem" viewBox="0 -960 960 960" width="2rem" fill="#d2d2d2"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
<div class="backdrop-menu position-fixed d-none vw-100 vh-100"></div>

<style>
    .menu-icon {
        cursor: pointer;
        z-index: 9999;
        position: fixed;
        top: 5px;
        left: 5px;
    }
    .side-menu {
        position: fixed;
        z-index: 99;
        top: 0;
        left: -310px;
        width: 300px;
        height: 100%;
        color: #d2d2d2;
        background-color: #2d2d2d;
        padding: 10px;
        padding-top: 40px;
        border-right: 1px solid #000000;
    }
    .menu-item {
        cursor: pointer;
        z-index: 999;
        padding: 10px;
        width: 100%;
    }
    .menu-item:hover {
        background-color: gray;
    }
    .backdrop-menu {
        position: fixed;
        z-index: 9;
        height: 100vh;
        width: 100vw;
        top: 0;
        left: 0;
        backdrop-filter: blur(5px);
    }
</style>

<script>
    $('.menu-icon').on('click', function(){
        if($('.side-menu').css('left') < '0') {
            $('#menu-bars').addClass('d-none');
            $('#menu-times').removeClass('d-none');
            $('.backdrop-menu').removeClass('d-none');
            $('#menu-times').animate({
                left: "260px",
            })
            $('.side-menu').animate({
                left: "0px",
            })
        }else{
            $('#menu-bars').removeClass('d-none');
            $('#menu-times').addClass('d-none');
            $('.backdrop-menu').addClass('d-none');
            $('#menu-times').animate({
                left: "0px",
            })
            $('.side-menu').animate({
                left: "-310px",
            })
        }
    });
    $('.main').on('click', function(){
        $('#menu-bars').removeClass('d-none');
        $('#menu-times').addClass('d-none');
        $('.side-menu').animate({
            left: "-310px",
        })
    });
</script>