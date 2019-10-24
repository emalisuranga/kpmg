import { ICity, IGNdivision, ICountry, IMember, ICheckAdminCompany } from './../models/general.model';
import { ICancelReason } from './../models/cancel-reason';
import { ICompanyType, IGetOtherDocs, IUpdateCourtDetails, IGetOtherDocsForName } from '../models/search.model';
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { APIConnection } from './connections/APIConnection';
import { IDocGroup } from '../models/doc.model';
import { IStatusCount, IRemoveOtherDoc, ICompanyReq } from '../models/recervationdata.model';
import { AuthService } from '../shared/auth.service';

import { Observable, of } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})


export class GeneralService {

  url: APIConnection = new APIConnection();

  constructor(
    private http: HttpClient,
    private Auth: AuthService
  ) { }

  getDocFeild(type: number, reqType: string = 'NAME_REG', companyId = null ): Observable<IDocGroup> {
    return this.http.post<IDocGroup>(this.url.getDocFeildAPI(), { type: type, req: reqType, companyId });
  }

  getComSubData(id: number): Observable<any> {
    return this.http.get<any>(this.url.getSubdataAPI() + id);
  }

  getStatusCount(): Observable<IStatusCount> {
    return this.http.post<IStatusCount>(this.url.getStatusCountAPI(), { email: this.Auth.getEmail() });
  }

  getCompanyType(): Observable<ICompanyType> {
    return this.http.get<ICompanyType>(this.url.getCompanyTypeAPI());
  }

  getDocumenttoServer(token: string, type: string = 'CAT_COMPANY_DOCUMENT') {
    return this.http.post(this.url.getDocumentDownloadAPI(), { token: token, type: type }, { responseType: 'arraybuffer' });
  }

  getDocName(token: string, type: string = 'CAT_COMPANY_DOCUMENT') {
    return this.http.post(this.url.getDocNameAPI(), { token: token, type: type });
  }

  getAuditorDocName(token: string, type: string = 'CAT_AUDITOR_DOCUMENT') {
    return this.http.post(this.url.getDocumentDownloadAPI(), { token: token, type: type }, { responseType: 'arraybuffer' });
  } 

  getSecretaryDocName(token: string, type: string = 'CAT_SECRETARY_DOCUMENT') {
    return this.http.post(this.url.getDocumentDownloadAPI(), { token: token, type: type }, { responseType: 'arraybuffer' });
  } 

  onDestroytoServer(token: string): Observable<any> {
    return this.http.delete<any>(this.url.getFileDestroyAPI() + '?token=' + token);
  }

  onReSubmitDestroytoServer(token: string): Observable<any> {
    return this.http.delete<any>(this.url.getReSubmitFileDestroyAPI() + '?token=' + token);
  }

  getNameChangeOtherDocList(data: IGetOtherDocs): Observable<IGetOtherDocs> {
    return this.http.post<IGetOtherDocs>(this.url.getOtherFileListAPI(), data);
  }

  getNameChangeOtherDocListForName(data: IGetOtherDocsForName): Observable<IGetOtherDocsForName> {
    return this.http.post<IGetOtherDocsForName>(this.url.getOtherFileListForNameAPI(), data);
  }
  removeNameChangeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.getOtherFileRemoveAPI(), data);
  }

  isResolutionDateExeed(data: ICompanyReq): Observable<ICompanyReq> {
    return this.http.post<ICompanyReq>(this.url.isResolutionDateExeedURL(), data);
  }
  isSetResolutionDate(data: ICompanyReq): Observable<ICompanyReq> {
    return this.http.post<ICompanyReq>(this.url.isSetResDateURL(), data);
  }


  updateCourtOrderDetails(data: IUpdateCourtDetails): Observable<IUpdateCourtDetails> {
    return this.http.post<IUpdateCourtDetails>(this.url.getUpdateCourtAPI(), data);
  }

  reductionPenalty(data: ICompanyReq): Observable<ICompanyReq> {
    return this.http.post<ICompanyReq>(this.url.getReductionPenalty(), data);
  }

  isCheckValisToken(token: string, type: string): Observable<any> {
    return this.http.post<any>(this.url.getValidTokenAPI(), { token: token, type: type, email: this.Auth.getEmail() });
  }

  getMember(id: string): Observable<IMember> {
    return this.http.post<IMember>(this.url.getMemberAPI(), { id: id });
  }

  getCancelReason(): Observable<ICancelReason[]> {
    return this.http.get<ICancelReason[]>('api/reason');
  }

  /*-------------heshan------------------*/
  getSocietyDocumenttoServer(token: string, type: string = 'CAT_SOCIETY_DOCUMENT') {
    return this.http.post(this.url.getDocumentDownloadAPI(), { token: token, type: type }, { responseType: 'arraybuffer' });
  }
  /*-------------heshan------------------*/

  getCapitalData(id: string) {
    return this.http.post<any>(this.url.getReduURL(), { id: id });
  }

  getSetting(key: string, type: string = 'type'): Observable<any> {
    return this.http.post<any>(this.url.getSettingAPI(), { key: key, type: type });
  }


  checkCompanByRegNumberForAdminReg(data: ICheckAdminCompany): Observable<ICheckAdminCompany> {
    return this.http.post<ICheckAdminCompany>(this.url.checkCompanyRegNumberForAdmin(), data);
  }

}
