/**
 * download url lite
 *
 * @author: legend(legendsky@hotmail.com)
 * @link: http://www.ugia.cn/?p=122
 * @version: 1.0
 *
 * @param string   url      
 * @param string   callback  回调函数
 * @param string  data      post数据
 *
 * @return void
 */
function downloadUrl(url, callback, data)
{
    // init
    url += url.indexOf("?") >= 0 ? "&" : "?";
    url += "random_download_url=" + Math.random();
    
    if (typeof data == 'undefined')
    {
        var data = null;
    }

    method = data ? 'POST' : 'GET';
    
	// create XMLHttpRequest object
	if (window.XMLHttpRequest)
	{
		var objXMLHttpRequest = new XMLHttpRequest();
	}
	else
	{
		var MSXML = ['MSXML2.XMLHTTP.6.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP'];
		for(var n = 0; n < MSXML.length; n ++)
		{
			try
			{
				var objXMLHttpRequest = new ActiveXObject(MSXML[n]);        
				break;
			}
			catch(e)
			{
			}
		}
	}
    
	// send request
    with(objXMLHttpRequest)
    {
        //setTimeouts(30*1000,30*1000,30*1000,30*60*1000);
        try
        {
            open(method, url, true);
            
            if (method == 'POST')
            {
                setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            }
            
            send(data);            
        }
        catch(e)
		{
			alert(e);
        }
        
		// on ready
        onreadystatechange = function()
        {
            if (objXMLHttpRequest.readyState == 4)
            {
                callback(objXMLHttpRequest.responseText, objXMLHttpRequest.status);
				delete(objXMLHttpRequest);
            }
        }
    }
}