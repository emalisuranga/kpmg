import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';
import { IIncorporationMembers, IMemberRemove, IOldMemberRemove, IMemberFile, IResubmitMemberChange } from '../models/director-secretary.model';

@Injectable({
  providedIn: 'root'
})
export class MemberChangeService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router,
    private http: HttpClient, ) { }

  // to load company director and secretary data...
  memberDataLoad(companyId: string, email: string): Observable<any> {
    return this.http.post(this.url.getCompanyMemberData(), { companyId: companyId, email: email });
  }
  memberResubmit(data: IResubmitMemberChange): Observable<IResubmitMemberChange> {
    return this.http.post<IResubmitMemberChange>(this.url.getCompanyMemberResubmitURL(), data);
  }
  // to save company director and secretary data...
  saveMemberData(data: IIncorporationMembers): Observable<IIncorporationMembers> {
    return this.http.post<IIncorporationMembers>(this.url.getMemberChangeDataSubmit(), data);
  }
  // to change details company director and secretary data...
  editMemberData(data: any): Observable<any> {
    return this.http.post(this.url.getMemberChangeDataEdit(), data);
  }
  // checkRegno(data: any): Observable<any> {
  //   return this.http.post(this.url.checkRegno(), data);
  // }
  revertMemberData(data: any): Observable<any> {
    return this.http.post(this.url.getMemberChangeDataRevert(), data);
  }
  inputSignby(data: any): Observable<any> {
    return this.http.post(this.url.inputSignby(), data);
  }
  form20CourtDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.form20CourtDataSubmit(), data);
  }
  // to download pdf files...
  memberPDF(memberId: string, type: string, reqId: string): Observable<any> {
    return this.http.post(this.url.getCompanyMemberPDF(), { memberId: memberId, type: type, requestId: reqId }, { responseType: 'arraybuffer' });
  }
  // to delete company members...
  removeMemberData(data: IMemberRemove): Observable<IMemberRemove> {
    return this.http.post<IMemberRemove>(this.url.getRemoveMemberURL(), data);
  }
  // to delete old company members...
  removeOldMemberData(data: IOldMemberRemove): Observable<IOldMemberRemove> {
    return this.http.post<IOldMemberRemove>(this.url.getRemoveMemberURL(), data);
  }
  // to delete uploaded pdf files...
  memberDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getMemberFileUploadedDelete(), data);
  }
  // to load uploaded pdf...
  memberFiles(data: IMemberFile): Observable<IMemberFile> {
    return this.http.post<IMemberFile>(this.url.getmemberFile(), data);
  }

}
