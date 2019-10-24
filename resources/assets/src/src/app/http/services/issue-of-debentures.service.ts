import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';
import { DataService } from '../../storage/data.service';

@Injectable({
  providedIn: 'root'
})
export class IssueOfDebenturesService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // Load previous debentures record on bganing...
  loadPreApproved(data: any): Observable<any> {
    return this.http.post(this.url.getPreApproved(), data);
  }

  // Load procesing List on beganing...
  loadProcesingList(data: any): Observable<any> {
    return this.http.post(this.url.getProcesingList(), data);
  }

  // Debentures details submit...
  debenturesSubmit(data: any): Observable<any> {
    return this.http.post(this.url.debenturesSubmit(), data);
  }

  // issue of debentures Form 10A download
  getIssueofDebenturesFormPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofDebenturesApplicationDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  // to delete uploaded issue-of-debentures pdf files...
  issueofdebenturesDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofDebenturesFileUploadedDelete(), data);
  }

  // After pdf file delete to view remaining pdf files
  issueofdebenturesFiles(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofDebenturesFile(), data);
  }

  // to delete updated uploaded issue-of-shares pdf files...
  issueofDebenturesDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getIssueofDebenturesFileUploadedDeleteUpdate(), data);
  }

  // complete resubmit process...
  issueofdebenturesReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getissueofDebenturesReSubmitAPI(), data);
  }

}
