import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';
import { ISocietyData } from '../models/society.model';

@Injectable({
  providedIn: 'root'
})
export class SocietyService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }


  // to submit society data...
  societyDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyDataSubmit(), data);
  }

  societyDataSubmitUpdate(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyDataSubmitUpdate(), data);
  }

  societyReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyReSubmit(), data);
  }

  getPathCon(data: any): Observable<any> {
    return this.http.post(this.url.getPathCon(), data);
  }

  // to load registered secretary profile card...
  societyProfile(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyProfileData(), data);
  }

  // to society payments...
  societyPay(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyPay(), data);
  }

  // to download affidavit pdf...
  getDocumenttoServer(token: string) {
    return this.http.post(this.url.getSocietyDocumentDownloadAPI(), { token: token }, { responseType: 'arraybuffer' });
  }

  getPDFService(mainmemberid, societyid): Observable<any> {
    return this.http.post(this.url.getSocietyDocumentDownloadAPI(), { mainmemberid: mainmemberid, societyid: societyid }, { responseType: 'arraybuffer' });
  }

  getApplicationPDFService(societyid): Observable<any> {
    return this.http.post(this.url.getSocietyApplicationDownloadAPI(), { societyid: societyid }, { responseType: 'arraybuffer' });
  }

  memberload(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyMemberData(), data);
  }

  memberloadwithaddress(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyMemberDataWithAddress(), data);
  }

  societyFiles(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyFile(), data);
  }

  societyFilesComments(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyFileComment(), data);
  }

  // to delete uploaded society pdf files...
  societyDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyFileUploadedDelete(), data);
  }

  societyDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyFileUploadedDeleteUpdate(), data);
  }

  // to load society  data using id...
  societyData(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyData(), data);
  }

  // to load society  comments using id...
  societyComments(data: any): Observable<any> {
    return this.http.post(this.url.getSocietyComments(), data);
  }






}
