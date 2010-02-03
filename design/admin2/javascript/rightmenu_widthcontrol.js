jQuery(function( $ )
{

    var link = $('#rightmenu-showhide'), rightmenu = $( '#rightmenu' ), timeout = null;

    link.attr('href', 'JavaScript:void(0);').html( rightmenu.width() <= 22 ? '&laquo;' : '&raquo;' ).click(function()
    {
        if ( timeout !== null )
        {
            clearTimeout( timeout );
            timeout = null;
        }
        var link = $( this ), rightmenu = $( '#rightmenu' ), hidden = rightmenu.width() < 22;
        if ( hidden )
        {
            $('#maincolumn').css( 'marginRight', '14em' );
            rightmenu.animate({
                width: '14em'
            }, 650, 'swing', function(){
                timeout = setTimeout( saveRightMenuStatus, 500 );
            } );
        }
        else
        {
            rightmenu.animate({
                width: '1.2em'
            }, 650, 'swing', function(){
                $('#maincolumn').css( 'marginRight', '1.2em' );
                timeout = setTimeout( saveRightMenuStatus, 500 );
            } );
        }
        link.html( hidden ? '&raquo;' : '&laquo;' );
    });
    function saveRightMenuStatus()
    {
        var show  = $( '#rightmenu' ).width() < 22 ? '' : '1';
        $.post( $.ez.url.replace( 'ezjscore/', 'user/preferences/set_and_exit/admin_right_menu_show/' ) + show );
    }
});