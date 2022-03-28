function build with rootdocument data content

    create div node called news_card on rootdocument
    set property className of news_card to 'news-card'

    create h1 node called newsTitle on news_card
    create p node called newsContent on news_card

    set property innerHTML of newsTitle to content.title
    set property innerHTML of newsContent to content.content

end function