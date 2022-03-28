function build with rootdocument data news_items

    create div node called newsList
    set property className of newsList to 'news-list'
    foreach news_items as news_item for spawn NewsItem on rootdocument using news_item


end function