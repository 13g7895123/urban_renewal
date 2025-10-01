1. 如果想要不影響正式站的情況下，要多一個.env讓我在local開發可以使用，可以怎麼做，檢查一下為甚麼local啟用失敗了
2. 移除frontend/node_modules與backend/writable/debugbar的git trakker，並更新.gitignore
3. /tables/urban-renewal幫我確認這一頁的資料與API是否正常，表格的部分幫我於頁碼的右側多一顆重新整理圖案的按鈕
4. 目前他打得API是http://localhost:3303/urban-renewals這一支，並且顯示404，請確認後端環境的docker啟用是否正常，並且.env的相關設定是否有正確，現在會有CORS錯誤
5. 請幫我找一下每個頁面的表格，都加一顆重新整理圖案的按鈕在右下
6. /tables/urban-renewal/1/property-owners這個頁面，我點資料的基本資料沒有任何反應
7. /tables/urban-renewal/1/property-owners/create這一頁確認一下，我點下去他一直載入資料中
8. /tables/urban-renewal我點基本資料會寫載入失敗
9. /tables/urban-renewal我點重新載入會失敗，打的是這支/api/urban-renewals
10. 幫我給這個前端一個沒人會用的PORT，並確保後端資料可以正常取得，為甚麼API是打這一支http://localhost:9228/urban-renewals，目前還是錯的欸，請確保前後端可以正常串在一起，幫我確認目前local的docker port 是不是搞錯API路徑了，我看docker後端是4002PORT，到底在幹嘛
11. http://localhost:7357/tables/urban-renewal這是我目前的網址，http://localhost:9228/api/urban-renewals這是我打的API，幫我確認為甚麼無法使用
