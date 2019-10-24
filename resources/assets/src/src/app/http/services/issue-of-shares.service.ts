import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

import { IShareType } from '../models/shares.model'; // import ISHaretype model
import { DataService } from '../../storage/data.service';

@Injectable({
  providedIn: 'root'
})
export class IssueOfSharesService {

  public isharetypes: IShareType;
  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // to load address  data using company id...
  loadCompanyName(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyName(), data);
  }

  // to load address  data using company id...
  loadprocessingList(data: any): Observable<any> {
    return this.http.post(this.url.getProcessingList(), data);
  }

  // submit data
  sharesSubmit(data: any): Observable<any> {
    return this.http.post(this.url.sharesSubmit(), data);
  }

  // load shareType
  initializeApp(): Promise<IShareType> {
    return this.http.get<IShareType>(this.url.getShareTypeAPI())
      .toPromise()
      .then(req => {
        return this.isharetypes = req;
    });
  }

  // load two csvs
  getCSVs(): Observable<any>{
    return this.http.get(this.url.getCSVsAPI());
  }

  // issue of share Form 6 download
  getIssueofShareFormPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofShareApplicationDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  // Current shareholders details pdf download
  getCurrentShareholdersDetailsPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getCurrentShareholdersDetailsPDFDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  // to delete uploaded issue-of-shares pdf files...
  issueofsharesDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofSharesFileUploadedDelete(), data);
  }

  // After pdf file delete to view remaining pdf files
  issueofsharesFiles(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofSharesFile(), data);
  }

  // Load excell data when ISSUE OF SHARES PROCESSING
  loadExcellData(data: any): Observable<any> {
    return this.http.post(this.url.getExcellDataloadAPI(), data);
  }

  // Reset shareholder records from database
  getResetShareholderRecordsService(data: any): Observable<any> {
    return this.http.post(this.url.getShareholdersRecordResetAPI(), data);
  }

  // Resubmit data
  sharesReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.sharesReSubmit(), data);
  }
  // to delete updated uploaded issue-of-shares pdf files...
  issueofsharesDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofSharesFileUploadedDeleteUpdate(), data);
  }

  // complete resubmit process...
  issueofsharesReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getissueofsharesReSubmitAPI(), data);
  }

}
