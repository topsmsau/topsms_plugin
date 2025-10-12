const SmsPreview = ({ sender, smsMessage }) => {
    return (
        <div className='bg-iphone h-[496px] w-[345px] flex justify-center bg-no-repeat py-16'>
            <div className='iphone-message-popup w-[290px] h-fit rounded-lg flex flex-col'>
                <div className='bg-white w-full px-2 py-1 rounded-t-lg'>
                    <div className='bg-messageMock w-full h-10 bg-cover' />
                </div>
                <div className='p-4 pt-3 h-[350px] overflow-y-auto'>
                    <p className='font-medium text-black text-sm overflow-y-auto'>
                    {sender}
                    </p>
                    <div className='text-xs text-black break-words flex overflow-y-hidden max-h-[450px] flex-col '>
                    {smsMessage ? (
                        <p
                        className='mx-1 mt-3'
                        dangerouslySetInnerHTML={{
                            __html: smsMessage.replace(/\n/g, '<br />'),
                        }}
                        />
                    ) : (
                        <p></p>
                    )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SmsPreview;