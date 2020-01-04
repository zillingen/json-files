# JSON files

JSON-files is the Bolt CMS extension which allow to upload files through POST multipart/form-data requests.
The extension saves uploaded files in `public/files` folder. 
If you set `filename` field with subdirectory like `mysubdir/file.jpg` extension will create
folder and will save uploaded file into it. 

Accepts only one level sub folder against files directory.
For example, filename `folder/file.jpg` is right, but filename 
like `folder1/folder2/file.jpg` will throw error.

## Configuration

After install you need to configure access token. 
Extension checks this token in X-Auth-Token header against every requests.

Extension's config example:

```yaml
# app/config/extensions/jsonfiles.zillingen.yml 

# Base path
path: /api/files

# Authentication
auth:
  enabled: true
  access_token: ee0fa2EiSohfoowo0aekea0xohB3quoh
```

## Upload files

Upload into `public/files`

```bash
curl -X POST \
    -H "X-Auth-Token: ee0fa2EiSohfoowo0aekea0xohB3quoh" \
    -F "filename=bar.jpg" \
    -F "file=@48d56e29c95411ed.jpg" \
    http://mysite.com/api/files
```

Upload into subdirectory in `public/files`

```bash 
curl -X POST \
    -H "X-Auth-Token: ee0fa2EiSohfoowo0aekea0xohB3quoh" \
    -F "filename=foo/bar.jpg" \
    -F "file=@48d56e29c95411ed.jpg" \
    http://mysite.com/api/files
```
