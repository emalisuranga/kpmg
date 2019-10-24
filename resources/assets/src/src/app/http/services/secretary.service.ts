import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';
import { ISecretaryData, ISecretaryLoad, ISecretaryDataFirm, IDeletePdf, ISecretaryLoadProfile, ISecretaryPay, ISecretaryFile, IisSecretaryReg, ISecretaryResubmit, ISecretaryFirmResubmit, ISecretaryDataFirmUpdate } from '../models/secretary.model';

@Injectable({
  providedIn: 'root'
})
export class SecretaryService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }



  // to submit secretary individual data...
  secretaryDataSubmit(data: ISecretaryData): Observable<ISecretaryData> {
    return this.http.post<ISecretaryData>(this.url.getSecretaryDataSubmit(), data);
  }
  // to load secretary individual data using nic...
  secretaryData(data: ISecretaryLoad): Observable<ISecretaryLoad> {
    return this.http.post<ISecretaryLoad>(this.url.getSecretaryData(), data);
  }
  // to submit secretary firm and pvt limited data...
  secretaryFirmDataSubmit(data: ISecretaryDataFirm): Observable<ISecretaryDataFirm> {
    return this.http.post<ISecretaryDataFirm>(this.url.getSecretaryFirmDataSubmit(), data);
  }
  // to load secretary firm and pvt limited partner data using nic...
  secretaryFirmPartnerData(data: ISecretaryLoad): Observable<ISecretaryLoad> {
    return this.http.post<ISecretaryLoad>(this.url.getSecretaryFirmPartnerData(), data);
  }
  // to delete uploaded secretary individual and firm pdf files...
  secretaryDeleteUploadedPdf(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getSecretaryFileUploadedDelete(), data);
  }
  // to load registered secretary profile card...
  secretaryProfile(data: ISecretaryLoadProfile): Observable<ISecretaryLoadProfile> {
    return this.http.post<ISecretaryLoadProfile>(this.url.getSecretaryProfileData(), data);
  }
  // to secretary payments...
  secretaryPay(data: ISecretaryPay): Observable<ISecretaryPay> {
    return this.http.post<ISecretaryPay>(this.url.getSecretaryPay(), data);
  }
  // to load secretary firm and pvt limited uploaded pdf using secId...
  secretaryFiles(data: ISecretaryFile): Observable<ISecretaryFile> {
    return this.http.post<ISecretaryFile>(this.url.getSecretaryFile(), data);
  }
  // to check secretary registered...
  isSecretaryRegistered(data: IisSecretaryReg): Observable<IisSecretaryReg> {
    return this.http.post<IisSecretaryReg>(this.url.getSecretaryRegistered(), data);
  }
  // to download secretary individual pdf...
  secretaryPDF(secid: string): Observable<any> {
    return this.http.post(this.url.getSecretaryPDF(), { secid: secid }, { responseType: 'arraybuffer' });
  }
  // to download secretary firm and pvt pdf...
  secretaryFirmPDF(firmid: string): Observable<any> {
    return this.http.post(this.url.getSecretaryFirmPDF(), { firmid: firmid }, { responseType: 'arraybuffer' });
  }
  // to load secretary firm and pvt limited general comments using secId...
  secretaryCommentsLoad(data: ISecretaryFile): Observable<ISecretaryFile> {
    return this.http.post<ISecretaryFile>(this.url.getSecretaryComments(), data);
  }
  // to load secretary firm data for resubmit...
  secretaryFirmDataLoad(data: ISecretaryFirmResubmit): Observable<ISecretaryFirmResubmit> {
    return this.http.post<ISecretaryFirmResubmit>(this.url.getSecretaryFirmDataResubmit(), data);
  }
  // to update secretary firm data...
  secretaryFirmDataUpdate(data: ISecretaryDataFirmUpdate): Observable<ISecretaryDataFirmUpdate> {
    return this.http.post<ISecretaryDataFirmUpdate>(this.url.getSecretaryFirmDataUpdate(), data);
  }
  // to load secretary individual data for resubmit...
  secretaryDataLoad(data: ISecretaryResubmit): Observable<ISecretaryResubmit> {
    return this.http.post<ISecretaryResubmit>(this.url.getSecretaryDataResubmit(), data);
  }
  // to update secretary individual data...
  secretaryDataUpdate(data: ISecretaryData): Observable<ISecretaryData> {
    return this.http.post<ISecretaryData>(this.url.getSecretaryDataUpdate(), data);
  }
  // to load secretary pdf comments with pdf files for resubmit...
  secretaryDocCommentsLoad(data: ISecretaryFile): Observable<ISecretaryFile> {
    return this.http.post<ISecretaryFile>(this.url.getSecretaryDocComments(), data);
  }
  // to delete uploaded secretary pdf files...
   secretaryDeleteUploadedPdfResubmited(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getSecretaryFileUploadedDeleteResubmited(), data);
  }
  // to update secretary individual and firm status at end of resubmit...
  secretaryStatusUpdate(data: ISecretaryFile): Observable<ISecretaryFile> {
    return this.http.post<ISecretaryFile>(this.url.getSecretaryStatusUpdate(), data);
  }
  // to update secretary firm status at end of isExisting firm...
  // secretaryFirmStatusUpdate(data: any): Observable<any> {
  //   return this.http.post(this.url.getSecretaryFirmStatusUpdate(), data);
  // }
  // secretary certificate request
  secretaryCertificateRequest(data: any): Observable<any> {
    return this.http.post(this.url.secretaryCertificateRequest(), data);
  }

  // load pre secretary certificate request
  loadSecCerRequest(data: any): Observable<any> {
    return this.http.post(this.url.loadsecretaryCertificateRequest(), data);
  }
}
