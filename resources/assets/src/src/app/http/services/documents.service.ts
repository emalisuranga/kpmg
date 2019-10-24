import { APIConnection } from './connections/APIConnection';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DocumentsService {
  url: APIConnection = new APIConnection();

  constructor(
    private http: HttpClient
  ) { }

  uploadFiles(total_form) {
    const httpHeaders = new HttpHeaders({'Content-Type': 'multipart/form-data'});
    const options = { headers: httpHeaders};
    return this.http.post(this.url.setfileUploadAPI(), total_form);
  }

}
