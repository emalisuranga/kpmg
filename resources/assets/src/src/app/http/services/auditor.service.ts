import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';
import { IAuditorData, IAuditorLoadSL, IAuditorLoadNonSL, IDeletePdf, IAuditorPay, IAuditorLoadProfile, IAuditorFile, IAuditorDataFirm, IAuditorID, IAuditorDataFirmUpdate, IAuditorDataLoad } from '../models/auditor.model';


@Injectable({
  providedIn: 'root'
})
export class AuditorService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router,
    private http: HttpClient, ) { }


  // to load auditor srilankan individual data using id...
  auditorIndCardLoad(id: number): Observable<any> {
    return this.http.get(this.url.audIndcardload() + id);
  }
  // to load auditor firm srilankan individual data using id...
  auditorFirmCardLoad(id: number): Observable<any> {
    return this.http.get(this.url.audFirmcardload() + id);
  }

  // to submit auditor individual data...
  auditorDataSubmit(data: IAuditorData): Observable<IAuditorData> {
    return this.http.post<IAuditorData>(this.url.getAuditorDataSubmit(), data);
  }
  ////////////////////
  auditorChangeStatusUpdate(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorChangeStatusUpdate(), data);
  }
  auditorChangeDeleteUploadedPdfResubmited(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getAuditorChangeFileUploadedDeleteResubmited(), data);
  }
  changeTypeSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorChangeTypeSubmit(), data);
  }
  auditorChangeDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorChangeDataSubmit(), data);
  }
  auditorChangeFiles(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorChangeFile(), data);
  }
  ///////////////////////////////
  // to load auditor firm data...
  auditorFirmChangeDataLoad(data: IAuditorID): Observable<IAuditorID> {
    return this.http.post<IAuditorID>(this.url.getAuditorFirmChangeData(), data);
  }
  changeTypeSubmitFirm(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorFirmChangeTypeSubmit(), data);
  }
  auditorFirmChangeDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorFirmChangeDataSubmit(), data);
  }
  auditorFirmChangeDeleteUploadedPdfResubmited(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getAuditorFirmChangeFileUploadedDeleteResubmited(), data);
  }
  //////////////////////
  // to load auditor srilankan individual data using nic...
  auditorDataSL(data: IAuditorLoadSL): Observable<IAuditorLoadSL> {
    return this.http.post<IAuditorLoadSL>(this.url.getAuditorDataSL(), data);
  }
  // to load auditor non srilankan individual data using passport no...
  auditorDataNonSL(data: IAuditorLoadNonSL): Observable<IAuditorLoadNonSL> {
    return this.http.post<IAuditorLoadNonSL>(this.url.getAuditorDataNonSL(), data);
  }
  // to download auditor individual pdf...
  auditorPDF(audid: string): Observable<any> {
    return this.http.post(this.url.getAuditorPDF(), { audid: audid }, { responseType: 'arraybuffer' });
  }
  // to delete uploaded auditor pdf files...
  auditorDeleteUploadedPdf(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getAuditorFileUploadedDelete(), data);
  }
  // to auditor payments...
  auditorPay(data: IAuditorPay): Observable<IAuditorPay> {
    return this.http.post<IAuditorPay>(this.url.getAuditorPay(), data);
  }
  // to load registered auditor profile card...
  auditorProfile(data: IAuditorLoadProfile): Observable<IAuditorLoadProfile> {
    return this.http.post<IAuditorLoadProfile>(this.url.getAuditorProfileData(), data);
  }
  // to load auditor uploaded pdf using audId...
  auditorFiles(data: IAuditorFile): Observable<IAuditorFile> {
    return this.http.post<IAuditorFile>(this.url.getAuditorFile(), data);
  }
  // to submit auditor firm data...
  auditorFirmDataSubmit(data: IAuditorDataFirm): Observable<IAuditorDataFirm> {
    return this.http.post<IAuditorDataFirm>(this.url.getAuditorFirmDataSubmit(), data);
  }
  // to get auditor id from auditor_firm_partners table using firm id...
  getAuditorID(data: IAuditorID): Observable<IAuditorID> {
    return this.http.post<IAuditorID>(this.url.getAuditorID(), data);
  }
  // to download auditor firm pdf...
  auditorFirmPDF(audid: string, firmid: string): Observable<any> {
    return this.http.post(this.url.getAuditorFirmPDF(), { audid: audid, firmid: firmid }, { responseType: 'arraybuffer' });
  }
  // to load auditor firm data...
  auditorFirmDataLoad(data: IAuditorID): Observable<IAuditorID> {
    return this.http.post<IAuditorID>(this.url.getAuditorFirmData(), data);
  }
  // to update auditor firm data...
  auditorFirmDataUpdate(data: IAuditorDataFirmUpdate): Observable<IAuditorDataFirmUpdate> {
    return this.http.post<IAuditorDataFirmUpdate>(this.url.getAuditorFirmDataUpdate(), data);
  }
  // to load auditor individual data...
  auditorDataLoad(data: IAuditorDataLoad): Observable<IAuditorDataLoad> {
    return this.http.post<IAuditorDataLoad>(this.url.getAuditorData(), data);
  }
  auditorDataLoadForChange(data: IAuditorDataLoad): Observable<IAuditorDataLoad> {
    return this.http.post<IAuditorDataLoad>(this.url.getAuditorDataForChange(), data);
  }
  // to update auditor individual data...
  auditorDataUpdate(data: IAuditorData): Observable<IAuditorData> {
    return this.http.post<IAuditorData>(this.url.getAuditorDataUpdate(), data);
  }
  // to load auditor pdf comments with pdf files for resubmit...
  auditorDocCommentsLoad(data: IAuditorFile): Observable<IAuditorFile> {
    return this.http.post<IAuditorFile>(this.url.getAuditorDocComments(), data);
  }
  // to load auditor general comments...
  auditorCommentsLoad(data: IAuditorFile): Observable<IAuditorFile> {
    return this.http.post<IAuditorFile>(this.url.getAuditorComments(), data);
  }
  // to delete uploaded auditor pdf files...
  auditorDeleteUploadedPdfResubmited(data: IDeletePdf): Observable<IDeletePdf> {
    return this.http.post<IDeletePdf>(this.url.getAuditorFileUploadedDeleteResubmited(), data);
  }
  // to download auditor individual renewal pdf...
  auditorRenewalPDF(token: string): Observable<any> {
    return this.http.post(this.url.getAuditorRenewalPDF(), { token: token }, { responseType: 'arraybuffer' });
  }
  // to check auditor individual is registered auditor for renewal...
  auditorIsReg(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorIsReg(), data);
  }
  // to download auditor firm renewal pdf...
  auditorFirmRenewalPDF(token: string): Observable<any> {
    return this.http.post(this.url.getAuditorFirmRenewalPDF(), { token: token }, { responseType: 'arraybuffer' });
  }
  // to check auditor firm is registered for renewal...
  auditorFirmIsReg(data: any): Observable<any> {
    return this.http.post(this.url.getAuditorFirmIsReg(), data);
  }
  // to update auditor individual and firm status at end of resubmit...
  auditorStatusUpdate(data: IAuditorFile): Observable<IAuditorFile> {
    return this.http.post<IAuditorFile>(this.url.getAuditorStatusUpdate(), data);
  }

  auditorRenewalReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getauditorRenewalReSubmit(), data);
  }

}
