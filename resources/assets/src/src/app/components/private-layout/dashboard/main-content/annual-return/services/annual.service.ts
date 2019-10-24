import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIAnnualReturnConnection } from './connections/APIAnnualReturnConnection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import { ISubmitDirectors, ISubmitSecretories, ISubmitShareholders, ISubmitShareRegisters, ISubmitAnnualRecords, ISubmitAnnualAuditors, ISubmitAnnualCharges, ISubmitShareRecords, IremoveAnnualDoc, Iresubmit, IRemoveOtherDoc, IupdateCourtDetails, IsubmitShareholderRecords } from '../models/annualReturn.model';

@Injectable({
  providedIn: 'root'
})
export class AnnualService {

 url: APIAnnualReturnConnection = new APIAnnualReturnConnection();

  constructor(private router: Router, private http: HttpClient) { }

  annualData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getAnnualData(), data);
  }
  annualHeavyData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getAnnualHeavyData(), data);
  }
  annualNICcheck(data: INICchecker): Observable<INICchecker> {
    return this.http.post<INICchecker>(this.url.getStakeholderNICData(), data);
  }
  annualStep1Submit(data: IIncorporationDataStep1Data): Observable<IIncorporationDataStep1Data> {
    return this.http.post<IIncorporationDataStep1Data>(this.url.submitStep1URL(), data);
  }
  annualDirectorsSubmit(data: ISubmitDirectors): Observable<ISubmitDirectors> {
    return this.http.post<ISubmitDirectors>(this.url.submitDirectorsURL(), data);
  }
  annualSecretoriesSubmit(data: ISubmitSecretories): Observable<ISubmitSecretories> {
    return this.http.post<ISubmitSecretories>(this.url.submitSecretoriesURL(), data);
  }
  annualShareholdersSubmit(data: ISubmitShareholders): Observable<ISubmitShareholders> {
    return this.http.post<ISubmitShareholders>(this.url.submitShareholdersURL(), data);
  }
  annualShareRegisterSubmit(data: ISubmitShareRegisters): Observable<ISubmitShareRegisters> {
    return this.http.post<ISubmitShareRegisters>(this.url.submitShareRegisterURL(), data);
  }
  annualRecordsSubmit(data: ISubmitAnnualRecords): Observable<ISubmitAnnualRecords> {
    return this.http.post<ISubmitAnnualRecords>(this.url.submitAnnualRecordURL(), data);
  }
  annualAuditorSubmit(data: ISubmitAnnualAuditors): Observable<ISubmitAnnualAuditors> {
    return this.http.post<ISubmitAnnualAuditors>(this.url.submitAnnualAuditorRecordURL(), data);
  }
  annualChargeSubmit(data: ISubmitAnnualCharges): Observable<ISubmitAnnualCharges> {
    return this.http.post<ISubmitAnnualCharges>(this.url.submitAnnualChargeRecordURL(), data);
  }
  shareRecordSubmit(data: ISubmitShareRecords): Observable<ISubmitShareRecords> {
    return this.http.post<ISubmitShareRecords>(this.url.submitShareRecordURL(), data);
  }
  removeDoc(data: IremoveAnnualDoc): Observable<IremoveAnnualDoc> {
    return this.http.post<IremoveAnnualDoc>(this.url.removeDocsURL(), data);
  }
  resubmitProcess(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitProcess(), data);
  }
  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }
  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }

  submitShareholderShareTransfers(data: IsubmitShareholderRecords): Observable<IsubmitShareholderRecords> {
    return this.http.post<IsubmitShareholderRecords>(this.url.submitShareholderTransferURL(), data);
  }
}

