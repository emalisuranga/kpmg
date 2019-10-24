import { NonauthGuard } from './../../../http/guards/nonauth.guard';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { Routes, RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { ReqAuthPasswordGuard } from 'src/app/http/guards/req-auth-password.guard';

const routes: Routes = [
  { path: '', redirectTo: 'request/link', pathMatch: 'full' },
  {
    path: 'request/link',
    component: ForgotPasswordComponent,
    canActivate: [NonauthGuard]
  },
  {
    path: 'password/reset',
    component: ResetPasswordComponent,
    canActivate: [ReqAuthPasswordGuard, NonauthGuard]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PasswordRoutingModule { }
