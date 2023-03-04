... ...
    
@route("/readfile")
def index():
    file_path = request.query['path']
    requested_path = os.path.join(os.getcwd() + file_path)
 
    with open(requested_path, 'r') as f: 
        content = f.read()    
        return content  

... ...