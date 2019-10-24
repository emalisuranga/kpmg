import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { IIncorporationData, IIncorporationMembers, IIncorporationDataStep1Data, IgetCompnies, IirdInfo, IverifyCompany, ISaveLabourRecord } from '../models/incorporation.model';
import { Observable } from 'rxjs';
import { INICchecker, IStakeholderDelete, ISecForDirDelete, IShForDirDelete, IShForSecDelete, IFileRemove } from '../models/stakeholder.model';

@Injectable({
  providedIn: 'root'
})
export class IncorporationService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  /* ---------------------- Udara Madushan -------------------------*/
  incorporationData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getIncorporationData(), data);
  }

  /* ---------------------- Udara Madushan -------------------------*/
  incorporationDataStep1Submit(data: IIncorporationDataStep1Data): Observable<IIncorporationDataStep1Data> {
    return this.http.post<IIncorporationDataStep1Data>(this.url.getIncorporationDataStep1Submit(), data);
  }

  /* ---------------------- Udara Madushan -------------------------*/
  incorporationDataStep2Submit(data: IIncorporationMembers): Observable<IIncorporationMembers> {
    return this.http.post<IIncorporationMembers>(this.url.getIncorporationDataStep2Submit(), data);
  }

  /* ---------------------- Udara Madushan -------------------------*/
  incorporationNICcheck(data: INICchecker): Observable<INICchecker> {
    return this.http.post<INICchecker>(this.url.getIncorporationNICCheckURL(), data);
  }

   /* ---------------------- Udara Madushan -------------------------*/
   incorporationDeleteStakeholder(data: IStakeholderDelete): Observable<IStakeholderDelete> {
    return this.http.post<IStakeholderDelete>(this.url.getIncorporationRemoveStakeholderURL(), data);
  }
   /* ---------------------- Udara Madushan -------------------------*/
  incorpPay(data: any): Observable<any> {
    return this.http.post(this.url.incorparationPay(), data);
  }

/* ---------------------- Udara Madushan -------------------------*/
    incorpSecForDirRemove(data: ISecForDirDelete): Observable<ISecForDirDelete> {
      return this.http.post<ISecForDirDelete>(this.url.incorparationSecForDirDeleteURL(), data);
}
 /* ---------------------- Udara Madushan -------------------------*/
 incorpShForDirRemove(data: IShForDirDelete): Observable<IShForDirDelete> {
  return this.http.post<IShForDirDelete>(this.url.incorparationShForDirDeleteURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpShForSecRemove(data: IShForSecDelete): Observable<IShForSecDelete> {
  return this.http.post<IShForSecDelete>(this.url.incorparationShForSecDeleteURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpShFirmForSecFirmRemove(data: IShForSecDelete): Observable<IShForSecDelete> {
  return this.http.post<IShForSecDelete>(this.url.incorparationShFirmForSecFirmDeleteURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpShFirmRemove(data: IShForSecDelete): Observable<IShForSecDelete> {
  return this.http.post<IShForSecDelete>(this.url.incorparationDeleteShFirmURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpSecFirmRemove(data: IShForSecDelete): Observable<IShForSecDelete> {
  return this.http.post<IShForSecDelete>(this.url.incorparationSecFirmDeleteURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpResubmit(data: any): Observable<any> {
  return this.http.post(this.url.incorparationResubmitURL(), data);
}

incorpForgeignRequestApproval(data: any): Observable<any> {
  return this.http.post(this.url.getForeignRequestApprovalAPI(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorpFileRemove(data: IFileRemove): Observable<IFileRemove> {
  return this.http.post<IFileRemove>(this.url.incorparationFileRemoveURL(), data);
}

 /* ---------------------- Udara Madushan -------------------------*/
 incorporationHeavyData(data: IIncorporationData): Observable<IIncorporationData> {
  return this.http.post<IIncorporationData>(this.url.incorparationHeavyDataURL(), data);
}

certifiedCopiesHeavyData(data: IIncorporationData): Observable<IIncorporationData> {
  return this.http.post<IIncorporationData>(this.url.certifiedCopiesHeavyDataURL(), data);
}

/* ---------------------- Udara Madushan -------------------------*/
incorporationSaveDocCopies(data: any): Observable<any> {
  return this.http.post<any>(this.url.incorporationSaveDocCopiesURL(), data);
}

getCompaniesForCertificates(data: IgetCompnies): Observable<IgetCompnies> {
  return this.http.post<IgetCompnies>(this.url.getCompaniesURL(), data);
}

incorporationSavePubDocCopies(data: any): Observable<any> {
  return this.http.post<any>(this.url.incorporationSavePubDocCopiesURL(), data);
}

incorporationSaveIRD(data: IirdInfo): Observable<IirdInfo> {
  return this.http.post<IirdInfo>(this.url.incorporationSaveIRDInfoURL(), data);
}

incorporationSaveLabourInfo(data: ISaveLabourRecord): Observable<ISaveLabourRecord> {
  return this.http.post<ISaveLabourRecord>(this.url.incorporationSaveLabourInfoURL(), data);
}

irdNICFileRemove(data: IFileRemove): Observable<IFileRemove> {
  return this.http.post<IFileRemove>(this.url.irdDirectorNICremoveURL(), data);
}


verifyCompany(data: IverifyCompany): Observable<IverifyCompany> {
  return this.http.post<IverifyCompany>(this.url.verfiyCompanyURL(), data);
}


certifiedCopyData(data: IIncorporationData): Observable<IIncorporationData> {
  return this.http.post<IIncorporationData>(this.url.getCertifiedCopyDataURL(), data);
}



}
