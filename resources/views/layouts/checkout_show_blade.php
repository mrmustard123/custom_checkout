@extends('layouts.app')

@section('title', 'Checkout - ' . $product['name'])

@section('content')
<checkout-form
    :product="{{ json_encode($product) }}"
    gateway="{{ $gateways[0]['name'] ?? 'dummy' }}"
></checkout-form>
@endsection
